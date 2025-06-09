<?php
require_once "db_connect.php";

$current_user = null;
$success = '';
$error = '';

//fetch user to display
$users = [];
$result = $conn -> query("SELECT id, name, email FROM users");
if($result){
    while($row = $result -> fetch_assoc()){
        $users [] = $row;
    }
}


//handle delete
if(isset($_GET['delete'])){
    $id = $_GET['delete'];
    $stmt = $conn -> prepare("DELETE FROM users WHERE id = ?");
    $stmt -> bind_param("i", $id);
    if($stmt -> execute()){
        $success = "User Deleted Successuly";
    }else{
        $error = "Error deleting user: " . $conn -> error;
    }
}


//fetch user for edit
if (isset($_GET['edit'])){
    $id = $_GET['edit'];
    $stmt = $conn -> prepare ("SELECT id, name , email FROM users WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt -> get_result();
    $current_user = $result->fetch_assoc();
}

?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage</title>
    <link href="../src/output.css" rel="stylesheet" >
</head>
<body>
    <div>
         <h1 class="flex text-2xl text-center font-bold">User Management</h1>
    </div>

    <?php if($success): ?>
        <div class="w-full bg-green-300 h-10" > <?php echo $success; ?></div>
    <?php endif; ?>

    <?php if($error): ?>
         <div class="w-full bg-red-300 h-10" > <?php echo $error; ?></div>

    <?php endif; ?>
    <table>
        <thead class="min-w-full divide-y divide-gray-200">
            <tr>
                <th class="px-4 py-2 text-left">Id</th>
                <th class="px-4 py-2 text-left">Name</th>
                <th class="px-4 py-2 text-left">Email</th>
                <th class="px-4 py-2 text-left">Actions</th>
            </tr>
        </thead>'
        <tbody>
            <?php foreach($users as $user): ?>
                <tr>
                    <td class="px-4 py-2 text-left"> <?php echo htmlspecialchars($user['id']); ?></td>
                    <td class="px-4 py-2 text-left"> <?php echo htmlspecialchars($user['name']); ?></td>
                    <td class="px-4 py-2 text-left"> <?php echo htmlspecialchars($user['email']); ?></td>
                    <td>
                        <a href="?edit=<?php echo $user['id']; ?>" 
                            onclick="document.getElementById('editModal').classList.remove('hidden')" 
                            class="text-blue-500 mr-3">Edit</a>                        
                        <a href="?delete=<?php echo $user['id']; ?>"
                            onclick="return confirm('Are you sure you want to delete this user?')"
                            class="text-red-500">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php if($current_user): ?>
    <div id="editModal" class="fixed inset-0 bg-black-600 bg-opacity-50 overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <h3>Update User</h3>
                <form method="POST" class="mt-2">
                    <input class="rounded-md w-full mb-4 px-4 py-2 border border-gray-300" type="text" name="id" value="<?php echo $current_user['id']; ?>">
                    <input class="rounded-md w-full mb-4 px-4 py-2 border border-gray-300" type="text" name="name" value="<?php echo htmlspecialchars($current_user['name']); ?>">
                    <input class="rounded-md w-full mb-4 px-4 py-2 border border-gray-300" type="text" name="email" value="<?php echo  htmlspecialchars($current_user['email']) ; ?>">
                    <input class="rounded-md w-full mb-4 px-4 py-2 border border-gray-300" type="password" name="password" placeholder="New Password (leave blank to keep current)" 
                               class="mb-4 w-full px-3 py-2 border border-gray-300 rounded-md">

                        <div class="flex justify-between">
                            <button type="button" 
                                    onclick="document.getElementById('editModal').classList.add('hidden');" 
                                    class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md">
                                Cancel
                            </button>
                            <button type="submit" name="update" 
                                    class="px-4 py-2 bg-blue-500 text-white rounded-md">
                                Update
                            </button>
                        </div>
                </form>
        </div>
    </div>
    <?php endif; ?>

    <script>

        window.onclick = function(event){
            if (event.target == document.getElementById('editModal')){
                document.getElementById('editModal').classList.add('hidden');
            }
        }
    </script>
</body>
</html>