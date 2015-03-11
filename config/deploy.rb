# config valid only for Capistrano 3.1
#puppet apply --modulepath=/root/setupServer/provision/modules /root/setupServer/provision/manifests/stage-box.pp
lock '3.2.1'

set :application, 'bjm-web'
set :repo_url, 'git@bitbucket.org:appsorama/bjm-web.git'

set :ssh_options, { forward_agent: true }

set :linked_dirs, %w{logs node_modules}

# Default branch is :master
# ask :branch, proc { `git rev-parse --abbrev-ref HEAD`.chomp }.call

# Default deploy_to directory is /var/www/my_app
# set :deploy_to, '/var/www/my_app'
set :deploy_to, '/var/www/bjm-web'

set :backup_to, 'backups'

#set :log_level, :error
set :log_level, :debug

set :pty, true

set :use_sudo, true

set :composer_install_flags, '--no-dev --quiet --optimize-autoloader'

#set :keep_releases, 5

SSHKit.config.command_map[:composer] = "#{shared_path.join("composer.phar")}"

def remote_file_exists?(full_path)
  'true' ==  capture("if [ -e #{full_path} ]; then echo 'true'; fi").strip
end

def get_cur_release_path
  cur_release = capture(:ls, '-xr', releases_path).split.first
  releases_path.join(cur_release)
end

def apply_puppet(puppet)
  cur_release = capture(:ls, '-xr', releases_path).split.first
  cur_release_path = releases_path.join(cur_release)
  env=fetch(:environment)
  deploypath="#{cur_release_path}/provision/manifests/#{puppet}"
  capture("sudo puppet apply --modulepath=#{cur_release_path}/provision/modules/ #{deploypath} ")
end

namespace :deploy do

  desc 'Install config.yml'
  task :install_config do
    on roles(:all) do |host|
      capture("ln -sf #{release_path}/config/#{fetch(:environment)}.yml #{release_path}/config/config.yml")
    end
  end

  desc 'npm install packeges'
  task :npm_install do
    on roles(:all) do |host|
      capture("cd #{release_path} && npm install")
      # capture("cd #{release_path} && npm install grunt")
    end
  end

  desc 'grunt compress'
  task :grunt_compress do
    on roles(:all) do |host|
      capture("cd #{release_path} && grunt")
    end
  end

  desc 'Set app version'
  task :set_version do
    on roles(:all) do |host|
      branch = fetch(:branch)
      ver = capture("cd #{repo_path} && git log --pretty=format:'' #{branch} | wc -l")
      capture("echo #{ver} > #{release_path}/config/ver")
    end
  end

  desc 'Create log file'
  task :create_log_file do
    on roles(:all) do |host|
      capture("touch #{release_path}/logs/#{fetch(:environment)}.log && chmod 666 #{release_path}/logs/#{fetch(:environment)}.log")
    end
  end

  desc 'Prepear prod server'
  task :setup_prod do
    on roles(:all) do |host|
      apply_puppet('production-box.pp')
    end
  end

  before  :updated,    'composer:install_executable'
  before  :updated,    'composer:install'
  before :updated, :npm_install
  before :updated, :grunt_compress
  after :updated, :install_config
  after :updated, :set_version
  after :updated, :create_log_file
end