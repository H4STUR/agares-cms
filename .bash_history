php artisan migrate
php artisan migrate
php artisan migrate
php artisan db:seed --class=RolesAndPermissionsSeeder
exit
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear
exit
npm run build
exit
php artisan db:seed --class=SettingsTableSeeder
php artisan db:seed --class=SettingsTableSeeder
php artisan db:seed --class=SettingsTableSeeder
exit
php artisan db:seed --class=SitesTableSeeder
php artisan db:seed --class=MenusTableSeeder
exit
php artisan storage:link
chmod -R 775 storage
chmod -R 775 public/storage
chown -R www-data:www-data storage
chown -R www-data:www-data public/storage
exit
php artisan storage:link
ls -l public/
rm public/storage
ls -l public/
php artisan storage:link
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage public
ls -l public/
sudo chown -R www-data:www-data public storage bootstrap/cache
chown -R www-data:www-data public storage bootstrap/cache
sudo chown -R www-data:www-data public storage bootstrap/cache
exit
curl http://localhost/storage/uploads/1732011387_Banner_diagnostyka_obrazowa.jpg
exit
