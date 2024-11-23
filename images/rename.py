import os
import re

def rename_files_to_numbering(directory):
    """
    Renames image files in the specified directory to a numbering format (001, 002, 003, ...),
    while keeping files already in the correct numbering format unchanged.

    Parameters:
    - directory (str): The directory containing the files to rename.
    """
    try:
        # Regex pattern to match files in numbering format (e.g., "001", "002")
        numbering_pattern = re.compile(r"^\d{3}$")

        # Get all files in the directory
        files = [f for f in os.listdir(directory) if os.path.isfile(os.path.join(directory, f))]
        
        # Filter image files (you can adjust the extensions as needed)
        image_files = [f for f in files if f.lower().endswith(('.png', '.jpg', '.jpeg', '.gif', '.bmp'))]
        
        # Collect files already in numbering format
        existing_numbers = sorted([
            int(os.path.splitext(f)[0]) for f in image_files if numbering_pattern.match(os.path.splitext(f)[0])
        ])
        
        # Determine the starting index for new numbering
        next_number = max(existing_numbers, default=0) + 1

        for file_name in image_files:
            base_name, extension = os.path.splitext(file_name)
            
            # Skip files already in numbering format
            if numbering_pattern.match(base_name):
                continue
            
            # Generate a new number and rename the file
            new_name = f"{next_number:03}{extension}"  # Format as "001", "002", etc.
            os.rename(
                os.path.join(directory, file_name),
                os.path.join(directory, new_name)
            )
            print(f"Renamed: {file_name} -> {new_name}")
            
            # Increment the next number
            next_number += 1

    except Exception as e:
        print(f"Error: {e}")

# Use the function in the current directory
if __name__ == "__main__":
    current_directory = os.getcwd()  # Get the current working directory
    rename_files_to_numbering(current_directory)
