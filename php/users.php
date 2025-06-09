<?php
require_once 'db_connect.php';

// Initialize variables
$error = '';
$success = '';
$current_user = null;

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $success = "User deleted successfully!";
    } else {
        $error = "Error deleting user: " . $conn->error;
    }
}

// Handle Create
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create'])) {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = password_hash($_POST['password'] ?? '', PASSWORD_DEFAULT);

    if ($name && $email && $password) {
        $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $password);
        if ($stmt->execute()) {
            $success = "User created successfully!";
        } else {
            $error = "Error creating user: " . $conn->error;
        }
    } else {
        $error = "All fields are required.";
    }
}

// Handle Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $id = $_POST['id'] ?? '';
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;

    if ($id && $name && $email) {
        if ($password) {
            $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?");
            $stmt->bind_param("sssi", $name, $email, $password, $id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
            $stmt->bind_param("ssi", $name, $email, $id);
        }
        
        if ($stmt->execute()) {
            $success = "User updated successfully!";
        } else {
            $error = "Error updating user: " . $conn->error;
        }
    } else {
        $error = "Name and email are required.";
    }
}

// Fetch user for editing
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT id, name, email FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $current_user = $result->fetch_assoc();
}

// Fetch users from database
$users = [];
$result = $conn->query("SELECT id, name, email FROM users");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Management</title>
    <link href="../src/output.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-6">
        <h1 class="text-2xl font-bold mb-4">User Management</h1>

        <!-- Success/Error Messages -->
        <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo $success; ?></span>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo $error; ?></span>
            </div>
        <?php endif; ?>

        <!-- Add User Button -->
        <button onclick="document.getElementById('createModal').classList.remove('hidden')" 
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mb-6">
            Add New User
        </button>

        <!-- Display Users -->
        <div class="bg-white shadow rounded">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left">ID</th>
                        <th class="px-4 py-2 text-left">Name</th>
                        <th class="px-4 py-2 text-left">Email</th>
                        <th class="px-4 py-2 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td class="px-4 py-2"><?php echo htmlspecialchars($user['id']); ?></td>
                            <td class="px-4 py-2"><?php echo htmlspecialchars($user['name']); ?></td>
                            <td class="px-4 py-2"><?php echo htmlspecialchars($user['email']); ?></td>
                            <td class="px-4 py-2">
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
        </div>

        <!-- Create User Modal -->
        <div id="createModal" class="hidden fixed inset-0 bg-red-600 bg-opacity-50 overflow-y-auto h-full w-full">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3 text-center">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Add New User</h3>
                    <form method="POST" class="mt-2">
                        <input type="text" name="name" placeholder="Name" required 
                               class="mb-2 w-full px-3 py-2 border border-gray-300 rounded-md">
                        <input type="email" name="email" placeholder="Email" required 
                               class="mb-2 w-full px-3 py-2 border border-gray-300 rounded-md">
                        <input type="password" name="password" placeholder="Password" required 
                               class="mb-4 w-full px-3 py-2 border border-gray-300 rounded-md">
                        <div class="flex justify-between">
                            <button type="button" 
                                    onclick="document.getElementById('createModal').classList.add('hidden')" 
                                    class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md">
                                Cancel
                            </button>
                            <button type="submit" name="create" 
                                    class="px-4 py-2 bg-blue-500 text-white rounded-md">
                                Create
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit User Modal -->
        <?php if ($current_user): ?>
        <div id="editModal" class="fixed inset-0 bg-black-600 bg-opacity-50 overflow-y-auto h-full w-full">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3 text-center">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Edit User</h3>
                    <form method="POST" class="mt-2">
                        <input type="hidden" name="id" value="<?php echo $current_user['id']; ?>">
                        <input type="text" name="name" placeholder="Name" required 
                               value="<?php echo htmlspecialchars($current_user['name']); ?>"
                               class="mb-2 w-full px-3 py-2 border border-gray-300 rounded-md">
                        <input type="email" name="email" placeholder="Email" required 
                               value="<?php echo htmlspecialchars($current_user['email']); ?>"
                               class="mb-2 w-full px-3 py-2 border border-gray-300 rounded-md">
                        <input type="password" name="password" placeholder="New Password (leave blank to keep current)" 
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
        </div>
        <?php endif; ?>

        <script>
            // Close modals when clicking outside
            window.onclick = function(event) {
                if (event.target == document.getElementById('createModal')) {
                    document.getElementById('createModal').classList.add('hidden');
                }
                if (event.target == document.getElementById('editModal')) {
                    document.getElementById('editModal').classList.add('hidden');
                }
            }
        </script>
    </div>
</body>
</html>