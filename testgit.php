<?php

#function gitReadFile($remote, $group, $repo, $$git='git')
function gitReadFile($host, $group, $repo, $fileName, $reference='HEAD', $git='git')
{
    $descriptorSpec = array(
        1 => array('pipe', 'w'),
        2 => array('pipe', 'w'),
    );
    $pipes = array();
    $command = sprintf(
        '%s archive --format=zip --remote git@%s:%s/%s.git %s %s',
        $git,
        $host,
        $group,
        $repo,
        $reference,
        $fileName
    );
    $resource = proc_open($command, $descriptorSpec, $pipes);
    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    foreach ($pipes as $pipe) {
        fclose($pipe);
    }
    $status = trim(proc_close($resource));
    if ($status) throw new Exception($stderr);
    if ($stderr != '') {
        throw new \Exception(sprintf('%s: %s', $command, $stderr));
    }
    if (strpos($stdout, 'fatal: no such ref:') > -1) {
        throw new \Exception(sprintf('%s: Unknown reference (tag or branch).', $command));
    }
    if (strpos($stdout, 'fatal: The remote end hung up unexpectedly.') > -1) {
        throw new \Exception(sprintf('%s: Problem connecting to remote.', $command));
    }
    if (strpos($stdout, 'remote: git upload-archive: archiver died with error.') > -1) {
        throw new \Exception(sprintf('%s: File not found.', $command));
    }
    if (strlen($stdout) <= 30) {
        throw new \Exception(sprintf('%s: Empty response.', $command));
    }
    $head = unpack("Vsig/vver/vflag/vmeth/vmodt/vmodd/Vcrc/Vcsize/Vsize/vnamelen/vexlen", substr($stdout, 0, 30));
    $data = gzinflate(substr($stdout, 30 + $head['namelen'] + $head['exlen'], $head['csize']));
    return $data;
}

#$data = gitReadFile('git.emag.ro', 'emgc', 'core-www', 'composer.json');

#echo $data;

echo gettype(true);
echo gettype('');
