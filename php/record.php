<?php
session_start();
require_once("db_connect.php");

$add_error = '';
$success = isset($_SESSION['success']) ? $_SESSION['success'] : '';
unset($_SESSION['success']); // Clear the message after displaying
$current_user = '';



$employees = [];
$result = $conn -> query("SELECT * FROM attendance");
if($result){
    while($row = $result -> fetch_assoc()){
        $employees [] = $row ;
    }
}
$users = [];
$result = $conn -> query("SELECT empID, empFName FROM employees");
if($result){
    while($row = $result -> fetch_assoc()){
        $users [] = $row;
    }
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['record'])){
    $recnum = $_POST['recnum'] ?? '';
    $code = $_POST['code'] ?? '';
    $date = $_POST['date'] ?? '';
    $timein = $_POST['in'] ?? '';
    $timeout = $_POST['out'] ?? '' ;

   // Combine date with time
    $datetimeIn = $date . ' ' . $timein;
    $datetimeOut = $date . ' ' . $timeout;

    if($recnum && $code && $timein && $timeout){
        $stmt = $conn -> prepare ("INSERT INTO attendance (attRN, empID, attDate, attTimeIn, attTimeOut) VALUES (?,?,?,?,?)");
        $stmt -> bind_param("iisss", $recnum , $code , $date ,  $datetimeIn, $datetimeOut );
        if($stmt -> execute()){
            $_SESSION['success'] = "Recorded successfully";
            header("Location: ". $_SERVER['PHP_SELF']);
            exit();
        }
        $stmt -> close();
    }
}
if(isset($_GET['cancel'])){
    $att = $_GET['cancel'];
    $stmt = $conn -> prepare("DELETE FROM attendance WHERE attRN = ?");
    $stmt -> bind_param("i", $att);
    if($stmt -> execute()){
            $_SESSION['success'] = "Recorded successfully";
            header("Location: ". $_SERVER['PHP_SELF']);
            exit();
    }
            $stmt -> close();

}
$employees = [];

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

if ($search) {
    $stmt = $conn->prepare("SELECT a.* FROM attendance a JOIN employees e ON a.empID = e.empID WHERE a.empID LIKE ? OR e.empFName LIKE ?");
    $likeSearch = "%" . $search . "%";
    $stmt->bind_param("ss", $likeSearch, $likeSearch);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM attendance");
}

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $employees[] = $row;
    }
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

  <form action="" method="GET" class="flex flex-row gap-4 mb-4">
    <input type="text" name="search" class="border border-gray-300 px-4 py-2 rounded-md" placeholder="Search by Employee ID or Name">
    <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md">Search</button>
    <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="px-4 py-2 bg-gray-300 rounded-md">Reset</a>
</form>
<?php if (empty($employees)): ?>
    <tr>
        <td colspan="5" class="text-center px-4 py-2">No records found.</td>
    </tr>
<?php endif; ?>

<form action="" id="department-modal" class="w-screen inset-0 h-screen bg-blue-200 flex flex-col items-center justify-center gap-4">
    <?php if (!empty($success)): ?>
        <div class="bg-green-300 w-full "> <?php echo $success ?></div>
    <?php endif; ?>

    <div class="flex flex-row gap-4">
        <a href="#" class="underline" id="adddept">add employee</a>
        <a href="attendancedash.php" class="underline" id="bck">back to menu</a>
    </div>
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-2 text-left">Record #</th>
                <th class="px-4 py-2 text-left">empID</th>
                <th class="px-4 py-2 text-left">Date/TimeIn</th>
                <th class="px-4 py-2 text-left">Date/TimeOut</th>
                <th class="px-4 py-2 text-left">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
        <?php foreach($employees as $employee):?>
            <tr>
                <td class="px-4 py-2 text-left"><?php echo htmlspecialchars($employee['attRN'])?></td>
                <td class="px-4 py-2 text-left"><?php echo htmlspecialchars($employee['empID'])?></td>
                <td class="px-4 py-2 text-left"><?php echo htmlspecialchars($employee['attTimeIn'])?></td>
                <td class="px-4 py-2 text-left"><?php echo htmlspecialchars($employee['attTimeOut'])?></td>
                <td class="px-4 py-2 text-left">
                    <a href="?cancel=<?php echo $employee['attRN']?>" class="unserline text-red-300">Cancel</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</form>

    <form action="" method="POST" id="adddepartment-modal" class="w-screen hidden h-screen flex flex-col items-center justify-center gap-4">
        <h1>Record Attendance Here</h1>

        <a href="#" id="close" class="px-4 py-3 rounded-md border border-gray-300 bg-blue-200">close</a>
        <div class="flex flex-row gap-4">
            <input type="number" name="recnum" class="border border-gray-300 px-4 py-2 rounded-md" placeholder="Record #" required>
            <select type="number" name="code" class="border border-gray-300 px-4 py-2 rounded-md" placeholder="employee" required>
                <?php foreach($users as $user): ?>
                        <option value="<?php echo $user['empID'] ?>" selected ><?php echo $user['empFName'] ?></option>
                <?php endforeach; ?>
            </select>
            <input type="date" name="date" class="border border-gray-300 px-4 py-2 rounded-md" placeholder="date/timeIn" required>
            <input type="time" name="in" class="border border-gray-300 px-4 py-2 rounded-md" placeholder="date/timeIn" required>
            <input type="time" name="out" class="border border-gray-300 px-4 py-2 rounded-md" placeholder="date/timeIn" required>
        </div>
        <div>
            <button type="submit" name="record" class="px-4 py-3 rounded-md border border-gray-300 bg-blue-200">Record</button>
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