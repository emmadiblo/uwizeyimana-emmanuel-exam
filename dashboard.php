
<?php
session_start();

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']);
if (!$is_logged_in) {
    header("location:index.php");
}

//database connection
$db_host = 'localhost';
$db_user = 'root'; 
$db_pass = ''; 
$db_name = 'numedev_personnel';


$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle logout
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit();
}


    // Add new employee
    if (isset($_POST['add_employee'])) {
        $nom =htmlspecialchars($_POST['nom']);
        $prenom =htmlspecialchars($_POST['prenom']);
        $fonction =htmlspecialchars($_POST['fonction']);
        $date_naissance =htmlspecialchars($_POST['date_naissance']);
        
        $stmt = $conn->prepare("INSERT INTO employees (nom, prenom, fonction, date_naissance) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nom, $prenom, $fonction, $date_naissance);
        $stmt->execute();
        
     
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
    
    // Update employee
    if (isset($_POST['edit_employee'])) {
        $id = (int)$_POST['employee_id'];
        $nom =htmlspecialchars($_POST['nom']);
        $prenom =htmlspecialchars($_POST['prenom']);
        $fonction =htmlspecialchars($_POST['fonction']);
        $date_naissance =htmlspecialchars($_POST['date_naissance']);
        
        $stmt = $conn->prepare("UPDATE employees SET nom = ?, prenom = ?, fonction = ?, date_naissance = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $nom, $prenom, $fonction, $date_naissance, $id);
        $stmt->execute();
        
     
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
    
    // Delete employee
    if (isset($_POST['delete_employee'])) {
        $id = (int)$_POST['employee_id'];
        
        $stmt = $conn->prepare("DELETE FROM employees WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
    
    // Get employee data for editing
    $employee_to_edit = null;
    if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
        $id = (int)$_GET['edit'];
        $stmt = $conn->prepare("SELECT * FROM employees WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $employee_to_edit = $result->fetch_assoc();
        }
    }
    
    // Get all employees
    $employees = [];
    $result = $conn->query("SELECT * FROM employees ORDER BY nom, prenom");
    while ($row = $result->fetch_assoc()) {
        $employees[] = $row;
    }


// Check if we need to show the delete confirmation modal
$show_delete_modal = isset($_GET['delete']) && is_numeric($_GET['delete']);
$delete_id = $show_delete_modal ? (int)$_GET['delete'] : null;

// format date
function format_date($date) {
    return date('d/m/Y', strtotime($date));
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Numedev - Gestion du Personnel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="tailwind/tailwind.js"></script>
    <link rel="stylesheet" href="tailwind/tailwind.css">
    
    <link rel="shortcut icon" href="images/logoMirego.png" type="image/x-icon">
    <style>
           html{
            font-family: arial !important
        }
    </style>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#5D5CDE',
                    }
                }
            },
            darkMode: 'class'
        }
        

    </script>
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-white min-h-screen">

<div class="flex flex-col min-h-screen">
    <header class="bg-primary text-white shadow">
        <div class="mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
            <h1 class="text-xl font-bold">Numedev - Gestion du Personnel</h1>
            <a href="?logout=1" class="px-3 py-1 rounded bg-white bg-opacity-20 hover:bg-opacity-30 text-sm font-medium">
                Déconnexion
            </a>
        </div>
    </header>

    <main class="flex-grow container mx-auto px-4 py-6 sm:px-6 lg:px-8">
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3 sm:mb-0">Liste des employes</h2>
            <a href="?add=1" class="flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-opacity-90">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
                Ajouter un employé
            </a>
        </div>

        <div class="overflow-x-auto bg-white dark:bg-gray-800 shadow rounded-lg">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Identifiant</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nom et Prénom</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Fonction</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date de Naissance</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <?php if (empty($employees)): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                            Aucun employé trouvé
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($employees as $employee): ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">2025_<?php echo $employee['id']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white"><?php echo htmlspecialchars($employee['nom']) . ' ' . htmlspecialchars($employee['prenom']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white"><?php echo htmlspecialchars($employee['fonction']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white"><?php echo format_date($employee['date_naissance']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <a href="?edit=<?php echo $employee['id']; ?>" class="text-primary hover:text-indigo-900 dark:hover:text-indigo-400">Modifier</a>
                                <a href="?delete=<?php echo $employee['id']; ?>" class="text-red-600 hover:text-red-900 dark:hover:text-red-400">Supprimer</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<!-- Modal to add/edit employee -->
<?php if (isset($_GET['add']) || isset($employee_to_edit)): ?>
<div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full mx-4 overflow-hidden">
        <div class="px-6 py-4 bg-primary text-white">
            <h3 class="text-lg font-medium">
                <?php echo isset($employee_to_edit) ? 'Modifier un employé' : 'Ajouter un employé'; ?>
            </h3>
        </div>
        
        <form class="p-6" method="POST" action="">
            <?php if (isset($employee_to_edit)): ?>
            <input type="hidden" name="employee_id" value="<?php echo $employee_to_edit['id']; ?>">
            <input type="hidden" name="edit_employee" value="1">
            <?php else: ?>
            <input type="hidden" name="add_employee" value="1">
            <?php endif; ?>
            
            <div class="space-y-4">
                <div>
                    <label for="nom" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nom</label>
                    <input type="text" id="nom" name="nom" required 
                           value="<?php echo isset($employee_to_edit) ? htmlspecialchars($employee_to_edit['nom']) : ''; ?>"
                           class="mt-1 block w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-base focus:outline-none focus:ring-primary focus:border-primary dark:text-white">
                </div>
                
                <div>
                    <label for="prenom" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Prénom</label>
                    <input type="text" id="prenom" name="prenom" required 
                           value="<?php echo isset($employee_to_edit) ? htmlspecialchars($employee_to_edit['prenom']) : ''; ?>"
                           class="mt-1 block w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-base focus:outline-none focus:ring-primary focus:border-primary dark:text-white">
                </div>
                
                <div>
                    <label for="fonction" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fonction</label>
                    <input type="text" id="fonction" name="fonction" required 
                           value="<?php echo isset($employee_to_edit) ? htmlspecialchars($employee_to_edit['fonction']) : ''; ?>"
                           class="mt-1 block w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-base focus:outline-none focus:ring-primary focus:border-primary dark:text-white">
                </div>
                
                <div>
                    <label for="date_naissance" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date de naissance</label>
                    <input type="date" id="date_naissance" name="date_naissance" required 
                           value="<?php echo isset($employee_to_edit) ? $employee_to_edit['date_naissance'] : ''; ?>"
                           class="mt-1 block w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-base focus:outline-none focus:ring-primary focus:border-primary dark:text-white">
                </div>
            </div>
            
            <div class="mt-6 flex justify-end space-x-3">
                <a href="<?php echo $_SERVER['PHP_SELF']; ?>" 
                   class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                    Annuler
                </a>
                <button type="submit" 
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-opacity-90">
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- Modal to delete confirm -->
<?php if ($show_delete_modal): ?>
<div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Confirmer la suppression</h3>
            <p class="text-gray-600 dark:text-gray-400">Êtes-vous sûr de vouloir supprimer cet employé ? Cette action est irréversible.</p>
            
            <div class="mt-6 flex justify-end space-x-3">
                <a href="<?php echo $_SERVER['PHP_SELF']; ?>" 
                   class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                    Annuler
                </a>
                <form method="POST" action="" class="inline">
                    <input type="hidden" name="employee_id" value="<?php echo $delete_id; ?>">
                    <input type="hidden" name="delete_employee" value="1">
                    <button type="submit" 
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700">
                        Supprimer
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>


</body>
</html>