src_dir='/tmp/build'
dest_dir='/var/www/html'
storage_dir='/var/www/storage'

# rsync -a --no-perms --no-owner --no-times /tmp/build/ /var/www/

echo "Starting migration..."

echo "Comparing file modification dates..."
# Compare the modification dates of the source and destination directories
# src_date=$(stat -c %Y ${src_dir})
# dest_date=$(stat -c %Y ${dest_dir})

# echo "Source directory modification date: $src_date"
# echo "Destination directory modification date: $dest_date"

# Get latest modified date from all files in the source directory
src_date_latest=$(find ${src_dir} -type f -exec stat -c %Y {} \; | sort | tail -n 1)
src_date_latest=${src_date_latest:-0}
# Get latest modified date from all files in the destination directory
dest_date_latest=$(find ${dest_dir} -type f -exec stat -c %Y {} \; | sort | tail -n 1)
dest_date_latest=${dest_date_latest:-0}

# Convert the modification dates to a human-readable format
src_date_readable=$(date -d @$src_date_latest +"%Y-%m-%d %H:%M:%S")
dest_date_readable=$(date -d @$dest_date_latest +"%Y-%m-%d %H:%M:%S")

echo "Latest source file modification date: $src_date_readable"
echo "Latest destination file modification date: $dest_date_readable"

# If the source directory has been modified more recently than the destination directory, proceed with the migration
if [ $src_date_latest -gt $dest_date_latest ]; then
  echo "Source directory has been modified more recently than the destination directory."
  echo "Proceeding with migration..."
else
  echo "Source directory has not been modified more recently than the destination directory."
  echo "Migration not required."
  exit 0
fi

echo "Script should take about 10 minutes to complete..."
echo "Deleting shared files... in 10...9...8..."

sleep 10

# Delete all files - including hidden ones
echo "Deleting all files in ${dest_dir}..."
# Use find with -not -name to exclude directories from the file count
initial_count=$(find ${dest_dir} -not -name '.*' | wc -l)
echo "Initial file count: $initial_count"
# Delate all files, excluding hidden files and directories
find ${dest_dir} -mindepth 1 -delete

sleep 10

# Count the number of files in the destination directory, excluding hidden files and directories
final_count=$(find ${dest_dir} -not -name '.*' | wc -l)
echo "Final file count: $final_count"

# Calculate the number of files deleted
deleted_count=$((initial_count - final_count))
echo "Deleted $deleted_count of $initial_count files."

# Count the number of files remaining in the destination directory
remaining_count=$((initial_count - deleted_count))

# Check if all files have been deleted
if [ $((remaining_count)) -eq 0 ]; then
  echo "All files have been deleted."
else
  echo "Not all files have been deleted. Remaining files:"
  ls -lA ${dest_dir}
fi

echo "Copying files..."
# Copy all files, including hidden ones, preserving directory structure
rsync -a --no-perms --no-owner --no-times ${src_dir}/ ${dest_dir}/

echo "Setting permissions..."
# Set permissions for app directory
find $dest_dir -mindepth 1 -type d -exec chmod 755 {} \;
find $dest_dir -mindepth 1 -type f -exec chmod 644 {} \;

echo "Copying storage (if it doesn't already exist)..."
# Copy storage directory if it doesn't already exist
if [ ! -d "${storage_dir}/app" ]; then
  echo "${storage_dir}/app Not Found - Copying storage directories..."
  cp -r ${dest_dir}/storage/app ${dest_dir}/storage/framework ${dest_dir}/storage/logs ${storage_dir}
  chmod -R 755 ${storage_dir}
	chown -R www-data:www-data ${storage_dir}/app ${storage_dir}/framework ${storage_dir}/logs

  echo "Copy complete, permissions set..."
  permissions=ls -lA ${storage_dir}
  echo "Permissions: $permissions"
else
  echo "Storage directories already exist."
fi

# Clear artisan caches
echo "Clearing caches..."
cd ${dest_dir}
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear


echo "Migration complete."
echo ""

sh /usr/local/bin/test-migration-complete.sh
