<?php

namespace ConfigToken\FileClient\Types;


use ConfigToken\ConnectionSettings\Types\LocalGitRepoConnectionSettings;
use ConfigToken\FileClient\Exception\ContentFormatException;
use ConfigToken\FileClient\Exception\FileClientConnectionException;
use ConfigToken\FileUtils;

class LocalGitRepoClient extends AbstractFileClient
{
    /**
     * Get the file server type identifier corresponding to the client's implementation.
     *
     * @return string
     */
    public static function getServerType()
    {
        return 'git';
    }

    /**
     * Call 'git archive --remote' to fetch the contents of a single file.
     *
     * @param string $hostName The host name of the Git repository.
     * @param string $groupName The group name of the Git repository.
     * @param string $repo The Git repository name.
     * @param string $fileName The file name from the Git repository
     * @param string $namedReference The named reference (tag or branch) for the Git repository.
     * @param string $git The local path of the "git" executable.
     * @return string
     * @throws FileClientConnectionException
     * @throws ContentFormatException
     */
    public static function gitArchiveFile($hostName, $groupName, $repo, $fileName, $namedReference='HEAD', $git='git')
    {
        $fileName = str_replace('\\', '/', $fileName);
        $descriptorSpec = array(
            1 => array('pipe', 'w'),
            2 => array('pipe', 'w'),
        );
        $pipes = array();
        $command = sprintf(
            '%s archive --format=zip --remote git@%s:%s/%s.git %s %s',
            $git,
            $hostName,
            $groupName,
            $repo,
            $namedReference,
            $fileName
        );
        $resource = proc_open($command, $descriptorSpec, $pipes);
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        foreach ($pipes as $pipe) {
            fclose($pipe);
        }
        $status = trim(proc_close($resource));
        if ($status) throw new FileClientConnectionException($stderr);
        if ($stderr != '') {
            throw new FileClientConnectionException(sprintf('%s: %s', $command, $stderr));
        }
        if (strpos($stdout, 'fatal: no such ref:') > -1) {
            throw new FileClientConnectionException(sprintf('%s: Unknown reference (tag or branch).', $command));
        }
        if (strpos($stdout, 'fatal: The remote end hung up unexpectedly.') > -1) {
            throw new FileClientConnectionException(sprintf('%s: Problem connecting to remote.', $command));
        }
        if (strpos($stdout, 'remote: git upload-archive: archiver died with error.') > -1) {
            throw new FileClientConnectionException(sprintf('%s: File not found.', $command));
        }
        if (strlen($stdout) <= 30) {
            throw new FileClientConnectionException(sprintf('%s: Empty response.', $command));
        }
        try {
            $head = unpack("Vsig/vver/vflag/vmeth/vmodt/vmodd/Vcrc/Vcsize/Vsize/vnamelen/vexlen", substr($stdout, 0, 30));
            $content = gzinflate(substr($stdout, 30 + $head['namelen'] + $head['exlen'], $head['csize']));
        } catch (\Exception $e) {
            throw new ContentFormatException(sprintf('%s: Bad response: %s.', $command, $e->getMessage()));
        }
        return $content;
    }

    /**
     * Return the file contents.
     *
     * @param string $fileName
     * @throws \Exception
     * @return array(string, string) [$contentType, $content]
     */
    public function readFile($fileName)
    {
        $this->validateConnectionSettings();
        /** @var LocalGitRepoConnectionSettings $connectionSettings */
        $connectionSettings = $this->getConnectionSettings();
        $content = $this->gitArchiveFile(
            $connectionSettings->getHostName(),
            $connectionSettings->getGroupName(),
            $connectionSettings->getRepoName(),
            $fileName,
            $connectionSettings->getNamedReference(),
            $connectionSettings->getGitExecutable()
        );
        $contentType = FileUtils::getContentTypeFromFileName($fileName);
        return array($contentType, $content);
    }
}