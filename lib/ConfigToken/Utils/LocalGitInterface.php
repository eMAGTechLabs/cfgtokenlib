<?php

namespace ConfigToken\Utils;


interface LocalGitInterface
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
                                             $namedReference='HEAD', $git='git');

    /**
     * Get the archive contents by executing a local git process.
     *
     * @param LocalProcessInterface $gitArchiveProcess
     * @return mixed
     */
    public static function getArchiveData(LocalProcessInterface $gitArchiveProcess);

    /**
     * Unpack and extract the file contents from the given archive contents.
     *
     * @param mixed $archiveData
     * @return mixed
     */
    public static function extractContents($archiveData);
}