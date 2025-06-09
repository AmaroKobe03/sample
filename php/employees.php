<?php
session_start();
require_once("db_connect.php");

$add_error = '';
$success = '';
$current_user = '';

//read
$users = [];
$result = $conn -> query("SELECT depCode, depName, depHead, depTelno FROM departments");
if($result){
    while($row = $result -> fetch_assoc()){
        $users [] = $row;
    }
}
//reacd
$employee = [];
$emp_result = $conn -> query("SELECT * FROM employees");
if($emp_result){
    while($row = $emp_result -> fetch_assoc()){
        $employee [] = $row;
    }
}


if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm'])){
    $empid = $_POST['empid'] ?? '';
    $code = $_POST['code'] ?? '';
    $lastname = $_POST['lastname'] ?? '';
    $firstname = $_POST['firstname'] ?? '';
    $rate = $_POST['rate'] ?? '';
    if($empid && $code && $lastname && $firstname && $rate){
        $stmt = $conn -> prepare("INSERT INTO employees (empID, depCode, empFName, empLName, empRPH) VALUES(?,?,?,?,?)");
        $stmt -> bind_param("iissi", $empid, $code, $lastname, $firstname, $rate);

        if($stmt->execute()){
            $_SESSION['success'] = "Employee Added Successfully";
            header("Location: ".$_SERVER['PHP_SELF']);
            exit();
        }else {
            $_SESSION['error'] = "Error updating department: " . $conn->error;
            header("Location: ".$_SERVER['PHP_SELF']);
            exit();
        }
        $stmt->close();

    }
}
if(isset($_GET['delete'])){
    $empID = $_GET['delete'];
    $stmt = $conn -> prepare ("DELETE FROM employees WHERE empID = ?");
    $stmt -> bind_param("i", $empID);

    if($stmt->execute()){
        $_SESSION['sucess'] = "Deleted employee Successfully";
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }
    $stmt -> close();
}

if(isset($_GET['edit'])){
    $empID = $_GET['edit'];
    $stmt = $conn -> prepare("SELECT empID, empFName, empLName, depCode, empRPH FROM employees WHERE empID = ?");
    $stmt -> bind_param('i', $empID);
    $stmt -> execute();
    $result = $stmt -> get_result();
    $current_user = $result -> fetch_assoc();
}
// Handle employee update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_employee'])) {
    $empID = $_POST['empID'] ?? '';
    $fname = $_POST['fname'] ?? '';
    $lname = $_POST['lname'] ?? '';
    $rph = $_POST['rph'] ?? '';
    $depCode = $_POST['depCode'] ?? '';
    $original_id = $_POST['original_id'] ?? '';
    $original_code = $_POST['original_code'] ?? '';

    if ($empID && $fname && $lname && $rph && $depCode && $original_id) {
        $stmt = $conn->prepare("UPDATE employees SET empID = ?, empFName = ?, empLName = ?, empRPH = ?, depCode = ? WHERE empID = ?");
        $stmt->bind_param("isssii", $empID, $fname, $lname, $rph, $depCode, $original_id);
        
        if($stmt->execute()){
            $_SESSION['success'] = "Employee updated successfully";
            header("Location: ".$_SERVER['PHP_SELF']);
            exit();
        } else {
            $_SESSION['error'] = "Error updating employee: " . $conn->error;
            header("Location: ".$_SERVER['PHP_SELF']);
            exit();
        }
        $stmt->close();
    } else {
        $_SESSION['error'] = "All fields are required";
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }
}

$sum_rph = 0;
foreach($employee as $employ) {
    $sum_rph += $employ['empRPH'];
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Management</title>
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
        <a href="#" class="underline" id="adddept">add employee</a>
        <a href="attendancedash.php" class="underline" id="bck">back to menu</a>
    </div>
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-2 text-left">ID</th>
                <th class="px-4 py-2 text-left">Dept</th>
                <th class="px-4 py-2 text-left">Lastname</th>
                <th class="px-4 py-2 text-left">Firstname</th>
                <th class="px-4 py-2 text-left">Rate/Hour</th>
                <th class="px-4 py-2 text-left">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
        <?php foreach($employee as $employ):?>
            <tr>
                <td class="px-4 py-2 text-left"><?php echo htmlspecialchars($employ['empID'])?></td>
                <td class="px-4 py-2 text-left"><?php echo htmlspecialchars($employ['depCode'])?></td>
                <td class="px-4 py-2 text-left"><?php echo htmlspecialchars($employ['empFName'])?></td>
                <td class="px-4 py-2 text-left"><?php echo htmlspecialchars($employ['empLName'])?></td>
                <td class="px-4 py-2 text-left"><?php echo htmlspecialchars(number_format($employ['empRPH'], 2))?></td>
                <td class="px-4 py-2 text-left">
                    <a href="?edit=<?php echo $employ['empID'] ?>"
                    onclick="document.getElementById('editModal').classList.remove('hidden')" 
                    class="underline text-blue-300">edit</a>
                    <a href="?delete=<?php echo $employ['empID']?>" class="unserline text-red-300">delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
        <!-- Add a row for the total -->
        <tr class="bg-gray-100 font-bold">
            <td class="px-4 py-2 text-left" colspan="4">Total RPH:</td>
            <td class="px-4 py-2 text-left"><?php echo htmlspecialchars(number_format($sum_rph, 2)) ?></td>
            <td class="px-4 py-2 text-left"></td>
        </tr>
        </tbody>
    </table>
</form>

    <form action="" method="POST" id="adddepartment-modal" class="w-screen hidden h-screen flex flex-col items-center justify-center gap-4">
        <h1>Add Employees</h1>

        <a href="#" id="close" class="px-4 py-3 rounded-md border border-gray-300 bg-blue-200">close</a>
        <div class="flex flex-row gap-4">
            <input type="number" name="empid" class="border border-gray-300 px-4 py-2 rounded-md" placeholder="ID" required>
            <select type="number" name="code" class="border border-gray-300 px-4 py-2 rounded-md" placeholder="Department" required>
                <?php foreach($users as $user): ?>
                        <option value="<?php echo $user['depCode'] ?>" selected ><?php echo $user['depName'] ?></option>
                <?php endforeach; ?>
            </select>
            <input type="text" name="lastname" class="border border-gray-300 px-4 py-2 rounded-md" placeholder="lastname" required>
            <input type="text" name="firstname" class="border border-gray-300 px-4 py-2 rounded-md" placeholder="firstname" required>
            <input type="number" name="rate" class="border border-gray-300 px-4 py-2 rounded-md" placeholder="Rate/Hour" required>
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
                    <input type="hidden" name="original_id" value="<?php echo $current_user['empID']; ?>">
                    <input type="hidden" name="original_code" value="<?php echo $current_user['depCode']; ?>">
                    
                    <input type="number" name="empID" value="<?php echo $current_user['empID']; ?>"
                    class="mb-2 w-full px-3 py-2 border border-gray-300 rounded-md">
                    
                    <input type="text" name="fname" placeholder="First Name" required 
                           value="<?php echo htmlspecialchars($current_user['empFName']); ?>"
                           class="mb-2 w-full px-3 py-2 border border-gray-300 rounded-md">
                           
                    <input type="text" name="lname" placeholder="Last Name" required
                           value="<?php echo htmlspecialchars($current_user['empLName']); ?>"
                           class="mb-2 w-full px-3 py-2 border border-gray-300 rounded-md">
                           
                    <input type="text" name="rph" placeholder="RPH" required 
                           value="<?php echo htmlspecialchars($current_user['empRPH']); ?>"
                           class="mb-2 w-full px-3 py-2 border border-gray-300 rounded-md">
                           
                    <input type="number" name="depCode" placeholder="Department Code" required
                           value="<?php echo htmlspecialchars($current_user['depCode']); ?>"
                           class="mb-2 w-full px-3 py-2 border border-gray-300 rounded-md">

                    <div class="flex justify-between">
                        <button type="button" 
                                onclick="document.getElementById('editModal').classList.add('hidden');" 
                                class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md">
                            Cancel
                        </button>
                        <button type="submit" name="update_employee" 
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