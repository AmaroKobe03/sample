<?php
session_start();
require_once("db_connect.php");

$add_error = '';
$success = '';
$current_user = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm'])) {
    $code = $_POST['code'] ?? '';
    $name = $_POST['name'] ?? '';
    $head = $_POST['head'] ?? '';
    $tele = $_POST['tele'] ?? '';

    if (empty($code) || empty($name) || empty($head) || empty($tele)) {
        $add_error = "All fields are required";
    } else {
        $stmt = $conn->prepare("INSERT INTO departments (depCode, depName, depHead, depTelNo) VALUES (?,?,?,?)");
        $stmt->bind_param("isss", $code, $name, $head, $tele);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Added Department Successfully";
            header("Location: ".$_SERVER['PHP_SELF']); // Redirect to same page
            exit();
        } else {
            $add_error = "Error adding department: " . $conn->error;
        }
        $_POST =[] ;
        $stmt->close();
    }
}

//hanlde update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $code = $_POST['code'] ?? '';
    $name = $_POST['name'] ?? '';
    $head = $_POST['head'] ?? '';
    $tele = $_POST['tele'] ?? '';
    $originalCode = $_POST['original_code'] ?? ''; // You need to pass the original code

    if ($code && $name && $head && $tele && $originalCode) {
        $stmt = $conn->prepare("UPDATE departments SET depCode = ?, depName = ?, depHead = ?, depTelNo = ? WHERE depCode = ?");
        $stmt->bind_param("isssi", $code, $name, $head, $tele, $originalCode);
        
        if($stmt->execute()){
            $_SESSION['success'] = "Department updated successfully";
            header("Location: ".$_SERVER['PHP_SELF']);
            exit();
        } else {
            $_SESSION['error'] = "Error updating department: " . $conn->error;
            header("Location: ".$_SERVER['PHP_SELF']);
            exit();
        }
        $stmt->close();
    }
}

//fetch user to display in the table
$users = [];
$result = $conn->query("SELECT depCode, depName, depHead, depTelNo  FROM departments");
if($result){
    while($row = $result -> fetch_assoc()){
        $users [] = $row;
    }
}

//delete
if(isset($_GET['delete'])){
    $code = $_GET['delete'];
    $stmt = $conn -> prepare("DELETE FROM departments WHERE depCode = ?");
    $stmt -> bind_param("i", $code);
    if($stmt->execute()){
        $_ESSION['success'] = "deleted successfully";
        header('Location: '.$_SERVER['PHP_SELF']);
        exit();
    }
}
//fetch user for editing
if(isset($_GET['edit'])){
    $code = $_GET['edit'];
    $stmt = $conn -> prepare("SELECT depCode, depName, depHead, depTelno FROM departments WHERE depCode = ?");
    $stmt -> bind_param("i", $code);
    $stmt -> execute();
    $result = $stmt -> get_result();
    $current_user = $result -> fetch_assoc();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Department</title>
    <link href="../src/output.css" rel="stylesheet">
</head>

<body id="body" class="h-screen w-screen flex flex-col items-center justify-center">
    <form action="" id="department-modal" class="w-screen inset-0 h-screen bg-blue-200 flex flex-col items-center justify-center gap-4">
        <?php if (!empty($success)): ?>
            <div class="bg-green-300 w-full "> <?php echo $success ?></div>
        <?php endif; ?>

        <?php if (!empty($add_error)): ?>
            <div class="bg-red-300 text-red-800 p-2 rounded w-full max-w-md text-center">
                <?php echo htmlspecialchars($add_error); ?>
            </div>
        <?php endif; ?>
        <div class="flex flex-row gap-4">
            <a href="#" class="underline" id="adddept">add department</a>

            <a href="attendancedash.php" class="underline" id="bck">back to menu</a>
        </div>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left">Code</th>
                    <th class="px-4 py-2 text-left">Name</th>
                    <th class="px-4 py-2 text-left">Head</th>
                    <th class="px-4 py-2 text-left">Tel.No</th>
                    <th class="px-4 py-2 text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
            <?php foreach($users as $user):?>
                <tr>
                    <td class="px-4 py-2 text-left"><?php echo htmlspecialchars($user['depCode'])?></td>
                    <td class="px-4 py-2 text-left"><?php echo htmlspecialchars($user['depName'])?></td>
                    <td class="px-4 py-2 text-left"><?php echo htmlspecialchars($user['depHead'])?></td>
                    <td class="px-4 py-2 text-left"><?php echo htmlspecialchars($user['depTelNo'])?></td>
                    <td class="px-4 py-2 text-left">
                        <a href="?edit=<?php echo $user['depCode'] ?>"
                        onclick="document.getElementById('editModal').classList.remove('hidden')" 
                        class="underline text-blue-300">edit</a>
                        <a href="?delete=<?php echo $user['depCode']?>" class="unserline text-red-300">delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>

            </tbody>
        </table>
    </form>
    <form action="" method="POST" id="adddepartment-modal" class="w-screen hidden h-screen flex flex-col items-center justify-center gap-4">

        <h1>Add Departments</h1>

        <a href="#" id="close" class="px-4 py-3 rounded-md border border-gray-300 bg-blue-200">close</a>
        <div class="flex flex-row gap-4">
            <input type="number" name="code" class="border border-gray-300 px-4 py-2 rounded-md" placeholder="Code" required>
            <input type="text" name="name" class="border border-gray-300 px-4 py-2 rounded-md" placeholder="name" required>
            <input type="text" name="head" class="border border-gray-300 px-4 py-2 rounded-md" placeholder="head" required>
            <input type="text" name="tele" class="border border-gray-300 px-4 py-2 rounded-md" placeholder="tel.No" required>
        </div>
        <div>
            <button type="submit" name="confirm" class="px-4 py-3 rounded-md border border-gray-300 bg-blue-200">Confirm</button>
        </div>
    </form>

    <?php if($current_user): ?>
        <div id="editModal" class="fixed inset-0 bg-black-600 bg-opacity-50 overflow-y-auto h-full w-full">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3 text-center">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Edit User</h3>
                    <form method="POST" class="mt-2">
                        <input type="hidden" name="original_code" value="<?php echo $current_user['depCode']; ?>">

                        <input type="number" name="code" value="<?php echo $current_user['depCode']; ?>"
                         class="mb-2 w-full px-3 py-2 border border-gray-300 rounded-md">
                        <input type="text" name="name" placeholder="Name" required 
                               value="<?php echo htmlspecialchars($current_user['depName']); ?>"
                               class="mb-2 w-full px-3 py-2 border border-gray-300 rounded-md">
                        <input type="text" name="head" placeholder="Email" required 
                               value="<?php echo htmlspecialchars($current_user['depHead']); ?>"
                               class="mb-2 w-full px-3 py-2 border border-gray-300 rounded-md">
                        <input type="tele" name="tele" placeholder="Telephone" 
                                value="<?php echo htmlspecialchars($current_user['depTelno']) ?>"
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
        const adddepartmentbtn = document.getElementById('adddept');
        const adddeptmodal = document.getElementById('adddepartment-modal');
        const closebtn = document.getElementById('close')

        adddepartmentbtn.addEventListener('click', (e) => {
            e.preventDefault();
            adddeptmodal.classList.remove('hidden');

        });
        closebtn.addEventListener('click', (e) => {
            e.preventDefault();
            adddeptmodal.classList.add('hidden');
        })
    </script>
    
        <script>
            // Close modals when clicking outside
            window.onclick = function(event) {

                if (event.target == document.getElementById('editModal')) {
                    document.getElementById('editModal').classList.add('hidden');
                }
            }
        </script>
    </div>
</body>

</html>