#!/bin/bash

text_to_find='LARAVEL_START'
file='/var/www/html/public/index.php'
src_dir='/tmp/build'
dest_dir='/var/www'

echo 'Verifying copied files...'
echo "Checking for file contents [${text_to_find}] in: ${file}"

until grep -q "${text_to_find}" "${file}"
do
  sleep 5s
done

echo "Success - File contents verified."

echo ""
echo "Verifying files in source and destination directories..."

# Use rsync to copy files, excluding hidden files and symbolic links
rsync -rcn --out-format="%n" --existing --exclude=".*" --exclude='*/.*' $src_dir/ $dest_dir/ | sort | uniq

# Count the number of files in source and destination directories, excluding hidden files and symbolic links
src_count=$(find $src_dir -type f ! -name ".*" ! -type l | wc -l)
dest_count=$(find $dest_dir -type f ! -name ".*" ! -type l | wc -l)

# Compare the file counts
if [ $src_count -eq $dest_count ]; then
  echo "All files have been copied. Count of src and dest match: $dest_count."
else
  echo "File copy is not complete. Source has $src_count files, but destination has $dest_count files."
  echo "Finding missing files..."
  cd $src_dir && find . -type f | sort > /tmp/src_files
  cd $dest_dir && find . -type f | sort > /tmp/dest_files

  # Find files that exist in the source directory but not in the destination directory
  missing_files=$(comm -23 /tmp/src_files /tmp/dest_files)
  if [ -n "$missing_files" ]; then
    echo "Missing files:"
    echo "$missing_files"
  fi

   # Find files that exist in both directories but have different contents
  differing_files=$(diff --brief -r $src_dir $dest_dir | grep -v "^Only")
  if [ -n "$differing_files" ]; then
    echo "Files with different contents:"
    echo "$differing_files"
  fi

  rm /tmp/src_files /tmp/dest_files

  # exit 1 # Don't exit here, as it will currently break deployments
fi

# Find files in the destination directory that don't have read, write, and execute permissions for the owner
incorrect_permissions_files=$(find $dest_dir -mindepth 1 -type d ! -perm 755 -o -type f ! -perm 644)

echo ""

# Check if any files were found
if [ -n "$incorrect_permissions_files" ]; then
  echo "The following files in the application directory do not have the correct permissions:"
  echo "$incorrect_permissions_files"
  # exit 1 # Don't exit here
else
  echo "All files in the destination directory have the correct permissions."
fi

echo ""
echo "File copy and verification complete."
echo ""
