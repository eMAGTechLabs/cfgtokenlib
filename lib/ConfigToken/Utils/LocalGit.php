<?php

namespace ConfigToken\Utils;


use ConfigToken\Utils\Exceptions\LocalGitException;

/**
 * Local git wrapper.
 *
 * Get the contents of a file from a Git repository via `git archive`.
 *
 * Usage:
 * $fileContents = LocalGit::extractContents(
 *     LocalGit::getArchiveData(
 *         new LocalProcess(
 *             LocalGit::getArchiveCommand(
 *                 'github.com', 'liutec', 'cfgtokenlib', 'ConfigToken/Utils/LocalGit.php'
 *             )
 *         )
 *     )
 * )
 */
class LocalGit implements LocalGitInterface
{
    /**
     * Get the `git archive` command needed to fetch the specified file from the remote repository.
     *
     * @param string $hostName The host name of the Git repository.
     * @param string $groupName The group name of the Git repository.
     * @param string $repo The Git repository name.
     * @param string $fileName The file name from the Git repository
     * @param string $namedReference The named reference (tag or branch) for the Git repository.
     * @param string $git The local path of the "git" executable.
     * @return string
     */
    public static function getArchiveCommand($hostName, $groupName, $repo, $fileName,
                                             $namedReference = 'HEAD', $git = 'git')
    {
        return sprintf(
            '%s archive --format=zip --remote git@%s:%s/%s.git %s %s',
            $git,
            $hostName,
            $groupName,
            $repo,
            $namedReference,
            FileUtils::replaceDirectorySeparator($fileName, '/')
        );
    }

    /**
     * Get the archive contents by executing a local git process.
     *
     * @param LocalProcessInterface $gitArchiveProcess
     * @throws LocalGitException
     * @return mixed
     */
    public static function getArchiveData(LocalProcessInterface $gitArchiveProcess)
    {
        list($status, $stdout, $stderr) = $gitArchiveProcess->executeAndGetResult();
        if ($status) throw new LocalGitException($stderr);
        if ($stderr != '') {
            throw new LocalGitException(sprintf('%s: %s', $gitArchiveProcess->getCommand(), $stderr));
        }
        $errorOutput = array(
            'fatal: no such ref:' => '%s: Unknown reference (tag or branch).',
            'fatal: The remote end hung up unexpectedly.' => '%s: Problem connecting to remote.',
            'remote: git upload-archive: archiver died with error.' => '%s: File not found.',
        );
        foreach ($errorOutput as $expected => $translation) {
            if (strpos($stdout, $expected) > -1) {
                throw new LocalGitException(sprintf($translation, $gitArchiveProcess->getCommand()));
            }
        }
        return $stdout;
    }

    /**
     * Unpack and extract the file contents from the given archive contents.
     *
     * @param mixed $archiveData
     * @throws LocalGitException
     * @return mixed
     */
    public static function extractContents($archiveData)
    {
        if (strlen($archiveData) <= 30) {
            throw new LocalGitException('Invalid archive data: too short for standard header.');
        }
        try {
            $head = unpack(
                "Vsig/vver/vflag/vmeth/vmodt/vmodd/Vcrc/Vcsize/Vsize/vnamelen/vexlen",
                substr($archiveData, 0, 30)
            );
            $content = gzinflate(substr($archiveData, 30 + $head['namelen'] + $head['exlen'], $head['csize']));
        } catch (\Exception $e) {
            throw new LocalGitException(sprintf('Unable to inflate archive data: %s.', $e->getMessage()));
        }
        return $content;
    }
}