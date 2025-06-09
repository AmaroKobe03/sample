<?php 
session_start();
require_once("connect.php");


$current_student = '';
$success = isset($_SESSION['success']) ? $_SESSION['success'] : '';
unset($_SESSION['success']);

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add'])){
    $id = $_POST['id'] ?? '' ;
    $lname = $_POST['lname'] ?? '' ;
    $fname = $_POST['fname'] ?? '' ;
    $mname = $_POST['mname'] ?? '' ;
    $school = $_POST['school'] ?? '' ;

    if(empty($id) && empty($lname) && empty($fname) && empty($mname) && empty($school)){
        $success = "empty fields detectede";
    }else{
        $stmt = $conn -> prepare("INSERT INTO students (id, lname, fame, mname, school) VALUES (?, ? , ?, ?, ?)");
        $stmt -> bind_param("issss", $id, $lname, $fname, $mname, $school);

        if($stmt-> execute()){
            $_SESSION['success'] = "added successfully";
            header("location: ". $_SERVER['PHP_SELF']);
            exit();
        }
        $stmt -> close();
    }
}
//fetch to display in table
$students = [];
$result = $conn -> query("SELECT * FROM students");
if($result){
    while($row = $result -> fetch_assoc()){
        $students [] = $row;
    }
}
//delete
if(isset($_GET['delete'])){
    $id = $_GET['delete'];
    $stmt = $conn -> prepare("DELETE FROM students WHERE id = ?");
    $stmt -> bind_param("i", $id);
    if($stmt -> execute()){
        $_SESSION['success'] = "DELETED SUCCESSFULLY";
        header("Location: ". $_SERVER['PHP_SELF']);
        exit();
    }
    $stmt -> close();
}
//fetch to edit
if(isset($_GET['edit'])){
    $id = $_GET['edit'];
    $stmt = $conn -> prepare("SELECT id, lname, fame, mname, school FROM students WHERE id = ?");
    $stmt -> bind_param("i", $id);
    $stmt -> execute();
    $result = $stmt -> get_result();
    $current_student = $result -> fetch_assoc();
}

//update
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])){
    $id = $_POST['id'] ?? '' ;
    $lname = $_POST['lname'] ?? '' ;
    $fname = $_POST['fname'] ?? '' ;
    $mname = $_POST['mname'] ?? '' ;
    $school = $_POST['school'] ?? '' ;
    $orig_id = $_POST['orig_id'] ?? '';

    if($id && $lname && $fname && $mname && $school && $orig_id){
        $stmt = $conn -> prepare("UPDATE students SET id = ?, lname = ?, fame = ?, mname =?, school =? WHERE id =?");
        $stmt -> bind_param("issssi", $id, $lname, $fname, $mname, $school, $orig_id);

        if($stmt -> execute()){
            $_SESSION['success'] = "udpated successfully";
            header("Location: ". $_SERVER['PHP_SELF']);
            exit();
        }
        $_POST=[];
        $stmt -> close();
    }
}
//search
$students =[];
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
if($search){
    $stmt = $conn -> prepare ("SELECT * FROM students WHERE id LIKE ? OR fame LIKE ?");
    $likesearch = "%" . $search . "%";
    $stmt -> bind_param("ss", $likesearch, $likesearch);
    $stmt -> execute();
    $result = $stmt -> get_result();
} else{
    $result = $conn -> query("SELECT * FROM students");
}
if($result){
    while($row = $result -> fetch_assoc()){
        $students [] = $row;
    }
}


?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students</title>
    <link href="../src/output.css" rel="stylesheet">
</head>
<body class="w-screen h-screen flex items-center justify-center flex-col gap-4">
    <form action="" method="GET" class="flex flex-row gap-4">
        <input type="test" name="search" placeholder="Search" class="px-6 py-3 border rounded-md border-gray-300">
        <button type="submit" class="px-4 py-2 bg-blue-500 rounded-md text-white">Search</button>
        <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="px-4 py-2 bg-gray-500 rounded-md text-black">Restart</a>
    </form>


    <?php if(!empty($success)): ?>
        <div><?php echo $success ?></div>
    <?php endif; ?>
    <form action="" method="POST" >
        <input name="id" type="number" class="px-4 py-2 border-2 border-gray-300" placeholder="id" required>
        <input name="lname" type="text" class="px-4 py-2 border-2 border-gray-300 " placeholder="Lname" required>
        <input name="fname" type="text" class="px-4 py-2 border-2 border-gray-300 " placeholder="Fname" required>
        <input name="mname" type="text" class="px-4 py-2 border-2 border-gray-300 " placeholder="Mname" required>
        <input  name="school" type="text" class="px-4 py-2 border-2 border-gray-300 " placeholder="school" required>
        <button type="submit" name="add" class="px-4 py-2 border bg-blue-600 rounded-md ">Add </button>
    </form>

<table class="min-w-full divide-y divide-gray-200 border border-gray-200">
    <thead class="bg-gray-50">
        <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border border-gray-200">ID</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border border-gray-200">Last Name</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border border-gray-200">First Name</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border border-gray-200">Middle Name</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border border-gray-200">School</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border border-gray-200">Actions</th>
        </tr>
    </thead>
    <tbody class="bg-white divide-y divide-gray-200">
        <?php foreach($students as $student): ?>
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border border-gray-200"><?php echo htmlspecialchars($student['id']) ?></td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border border-gray-200"><?php echo htmlspecialchars($student['lname']) ?></td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border border-gray-200"><?php echo htmlspecialchars($student['fame']) ?></td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border border-gray-200"><?php echo htmlspecialchars($student['mname']) ?></td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border border-gray-200"><?php echo htmlspecialchars($student['school']) ?></td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border border-gray-200">
                    <a class="text-blue-600" href="?edit=<?php echo $student['id'] ?>" 
                        onclick="document.getElementById('editmodal').classList.remove('hidden')"
                    >Edit</a>
                    <a href="?delete=<?php echo $student['id'] ?>" class="text-red-400" >Delete</a>
                </td>

            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
    <?php if($current_student): ?>
    <div id="editmodal" class="<?php echo isset($_GET['edit']) ? '' : 'hidden' ?>">

        <form action="" method="POST">
            <input name="orig_id" type="number" class="px-4 py-2 border-2 hidden border-gray-300" placeholder="id" 
                value="<?php echo htmlspecialchars($current_student['id']) ?>" 
                required>
            <input name="id" type="number" class="px-4 py-2 border-2 border-gray-300" placeholder="id" 
                value="<?php echo htmlspecialchars($current_student['id']) ?>" 
                required>
            <input name="lname" type="text" class="px-4 py-2 border-2 border-gray-300 " placeholder="Lname"  
                value="<?php echo htmlspecialchars($current_student['lname']) ?>"
                required>
            <input name="fname" type="text" class="px-4 py-2 border-2 border-gray-300 " placeholder="Fname"  
               value="<?php echo htmlspecialchars($current_student['fame']) ?>" required>
            <input name="mname" type="text" class="px-4 py-2 border-2 border-gray-300 " placeholder="Mname"  
                value="<?php echo htmlspecialchars($current_student['mname']) ?>" 
                required>
            <input  name="school" type="text" class="px-4 py-2 border-2 border-gray-300 " placeholder="school"  
                value="<?php echo htmlspecialchars($current_student['school']) ?>" 
                required>
            <button type="submit" name="update" class="px-4 py-2 border bg-blue-600 rounded-md ">update </button>
            <button type="button"
            onclick="document.getElementById('editmodal').classList.add('hidden')"
            name="add" class="px-4 py-2 border bg-blue-600 rounded-md ">Close </button>

        </form>
        </div>
    <?php endif; ?>

<!-- 
    <script>
        const edit = document.getElementById('editbtn');
        const modal = document.getElementById('editmodal');

        edit.addEventListener('click', (e) => {
            e.preventDefault();
            modal.classList.remove('hidden');
        }
    )
    </script> -->
</body>
</html>
