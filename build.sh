#!/bin/bash

# Function to delete .txt and .map files recursively
delete_txt_map() {
    local DIR="$1"

    # Check if a directory path is provided
    if [ -z "$DIR" ]; then
        echo "Error: No directory path provided."
        return 1
    fi

    # Check if the directory exists
    if [ ! -d "$DIR" ]; then
        echo "Error: Directory '$DIR' does not exist."
        return 1
    fi

    # Delete all .txt and .map files recursively
    find "$DIR" -type f \( -name "*.txt" -o -name "*.map" \) -delete

    # Check if the find command was successful
    if [ $? -eq 0 ]; then
        echo "All .txt and .map files have been deleted from $DIR."
    else
        echo "An error occurred while deleting files."
        return 1
    fi
}

# Function to handle copying and compressing
copy_and_compress() {
  local source_dir="$1"
  local destination_dir="$2"
  local exclude_folders="$3"
  local copy_list=("${@:4}")

  # Delete existing files in the destination directory
  rm -rf "$destination_dir"

  # Ensure the destination directory exists
  mkdir -p "$destination_dir"

  # Copy selected folders and files, conditionally excluding directories if not in dev build
  for item in "${copy_list[@]}"; do
    source_path="$source_dir/$item"
    destination_path="$destination_dir/$item"

    if [ -e "$source_path" ]; then
      if "$exclude_folders"; then
        rsync -av --exclude='psr' --exclude='fakerphp' --exclude='symfony' "$source_path" "$destination_dir"
        echo "Copied: $item (excluding psr, fakerphp, symfony)"
      else
        rsync -av "$source_path" "$destination_dir"
        echo "Copied: $item (no exclusions)"
      fi
    else
      echo "Warning: $item does not exist in the source directory."
    fi
  done

  echo -e "\nCopy completed."

  # Delete File app/ComposerScript.php
  # because it's not needed in the production build
  rm -rf "$destination_dir/app/ComposerScript.php"
  echo -e "\nDeleted: app/ComposerScript.php"

  delete_txt_map "$destination_dir/assets"

  # Run the zip command and suppress output
  cd "$destination_dir"
  cd ..
  local dest_dir_basename=$(basename "$destination_dir")
  zip -rq "${dest_dir_basename}.zip" "$dest_dir_basename" -x "*.DS_Store"

  cd .. # go back to fluent-booking plugin directory

  # Check for errors
  if [ $? -ne 0 ]; then
    echo "Error occurred while compressing."
    exit 1
  fi

  # Print completion message
  echo -e "\nCompressing Completed. builds/$(basename "$destination_dir").zip is ready. Check the builds directory. Thanks!\n"
}

# Parse command line arguments efficiently
nodeBuild=false
devBuild=false
withPro=false

for arg in "$@"; do
  case "$arg" in
    --node-build) nodeBuild=true ;;
    --dev_build)  devBuild=true ;;
    --with_pro)   withPro=true ;;
  esac
done

if "$nodeBuild"; then
  echo -e "\nBuilding fluentCrmContact Script\n"
  (cd resources/fluentCrmContact && npx mix --production)

  echo -e "\nBuilding FluentFormEditor Script\n"
  (cd resources/FluentFormEditor && npx mix --production)

  echo -e "\nBuilding Main App Frontend\n"
  npx mix --mix-config=public-webpack.mix.js --production

  echo -e "\nBuilding Main App\n"
  npx mix --production

  if "$devBuild"; then
    echo -e "\nOptimizing Composer Scripts (Dev Build)\n"
    composer dump-autoload --classmap-authoritative
  else
    echo -e "\nOptimizing Composer Scripts (Prod Build)\n"
    composer dump-autoload --classmap-authoritative
  fi
fi

# Copy and compress Fluent Booking
copy_and_compress "." "builds/fluent-booking" "" "app" "readme.txt" "assets" "boot" "config" "database" "language" "vendor" "composer.json" "fluent-booking.php" "index.php"

if "$withPro"; then
  echo -e "\Compressing Pro\n"
  # Copy and compress Fluent Booking Pro
  copy_and_compress "../fluent-booking-pro" "builds/fluent-booking-pro" "" "app" "boot" "config" "database" "language" "vendor" "composer.json" "fluent-booking-pro.php" "index.php" "readme.txt"
  echo -e "\Pro Addon done\n"
fi