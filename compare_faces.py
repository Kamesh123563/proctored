import sys
from deepface import DeepFace

def compare_faces(user_image_path, captured_image_path):
    try:
        # Compare faces using DeepFace
        result = DeepFace.verify(user_image_path, captured_image_path, model_name='Facenet')

        # Check if faces match
        if result["verified"]:
            return "Match"
        else:
            return "No match"
    
    except Exception as e:
        return "Error"

if __name__ == "__main__":
    if len(sys.argv) != 3:
        sys.exit("Usage: python compare_faces.py <user_image_path> <captured_image_path>")
    
    user_image_path = sys.argv[1]
    captured_image_path = sys.argv[2]

    # Call the compare_faces function and print the result
    result = compare_faces(user_image_path, captured_image_path)
    print(result)
