<?php
namespace Deployer;

require 'recipe/laravel.php';

// Project name
set('application', 'planner');

// Project repository
set('repository', 'git@github.com:theothernic/planner.git');

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', true);

// Shared files/dirs between deploys
add('shared_files', []);
add('shared_dirs', []);

// Writable dirs by web server
add('writable_dirs', []);
set('http_user', 'http');
set('http_group', 'webapps');

set('deploy_perms_files', '0660');
set('deploy_perms_dirs', '0770');


// Hosts

host('outbound.1902.us')
    ->stage('production')
    ->user('deploy')
    ->identityFile('~/.ssh/deploy_id_ed25519')
    ->set('deploy_path', '/data/opt/webapps/{{application}}');

// Tasks

task('build', function () {
    run('cd {{release_path}} && build');
});

task('perms:group', 'sudo chgrp -R {{http_group}} {{deploy_path}}');
task('perms:dirs', 'find {{deploy_path}} -type d ! -perm {{deploy_perms_dirs}} -exec sudo chmod {{deploy_perms_dirs}} {} \;');
task('perms:files', 'find {{deploy_path}} -type f ! -perm {{deploy_perms_files}} -exec sudo chmod {{deploy_perms_files}} {} \;');

task('perms', [
    'perms:dirs',
    'perms:files',
    'perms:group'
]);

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

// Migrate database before symlink new release.

before('deploy:symlink', 'artisan:migrate');

// set permissions on files and directories.
after('deploy:writable', 'perms');
