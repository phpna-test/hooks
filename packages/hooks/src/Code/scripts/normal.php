#!/usr/bin/env php
<?php
$branch = $argv[1];
shell_exec("git pull origin {$branch}");