set :application, 'clo-web-2015'
set :repo_url, 'git@git.gotenzing.com:clo/web-2015.git'

# Branch options
# Prompts for the branch name (defaults to current branch)
#ask :branch, -> { `git rev-parse --abbrev-ref HEAD`.chomp }

# Hardcodes branch to always be master
# This could be overridden in a stage config file
set :branch, :master

set :deploy_to, -> { "/var/www/html/production/#{fetch(:application)}" }

# Use :debug for more verbose output when troubleshooting
set :log_level, :info

# Put all shared files/directories here (e.g. uploads that need to go on the NFS drive)
set :linked_files, fetch(:linked_files, []).push('.env', 'wp-content/wp-cache-config.php', 'web/.htaccess')
set :linked_dirs, fetch(:linked_dirs, []).push('wp-content/uploads','wp-content/cache')

namespace :deploy do
  desc 'Restart application'
  task :restart do
    on roles(:app), in: :sequence, wait: 5 do
      # Your restart mechanism here, for example:
      # execute :service, :nginx, :reload
    end
  end
end

namespace :deploy do
  desc 'Sync servers'
  task :sync do
    on roles(:web), in: :sequence, wait: 5 do
      execute('syncit')
    end
  end
end

namespace :deploy do
  desc 'Sync servers'
  task :sync_again do
    on roles(:web), in: :sequence, wait: 5 do
      execute('syncit')
    end
  end
end

# The above restart task is not run by default
# Uncomment the following line to run it on deploys if needed
# after 'deploy:publishing', 'deploy:restart'

namespace :deploy do
  desc 'Update WordPress template root paths to point to the new release'
  task :update_option_paths do
    on roles(:app) do
      within fetch(:release_path) do
        if test :wp, :core, 'is-installed'
          [:stylesheet_root, :template_root].each do |option|
            # Only change the value if it's an absolute path
            # i.e. The relative path "/themes" must remain unchanged
            # Also, the option might not be set, in which case we leave it like that
            value = capture :wp, :option, :get, option, raise_on_non_zero_exit: false
            if value != '' && value != '/themes'
              execute :wp, :option, :set, option, fetch(:release_path).join('web/wp/wp-content/themes')
            end
          end
        end
      end
    end
  end
end

namespace :deploy do
  desc 'Install composer packages in root'
  task :install_env_packages do
    on roles(:web), in: :sequence, wait: 5 do
      execute "cd '#{release_path}'; composer install --no-dev --prefer-dist --no-interaction --quiet --optimize-autoloader"
    end
  end
end

namespace :deploy do
  desc 'Install composer packages in the theme'
  task :install_theme_packages do
    on roles(:web), in: :sequence, wait: 5 do
      execute "cd '#{release_path}/wp-content/themes/clo_web_2015'; composer install --no-dev --prefer-dist --no-interaction --quiet --optimize-autoloader"
    end
  end
end

# The above update_option_paths task is not run by default
# Note that you need to have WP-CLI installed on your server
# Uncomment the following line to run it on deploys if needed
# after 'deploy:publishing', 'deploy:update_option_paths'

after 'deploy:updated', 'deploy:sync'
after 'deploy:updated', 'deploy:install_env_packages'
after 'deploy:updated', 'deploy:install_theme_packages'
after 'deploy:finished', 'deploy:sync_again'
