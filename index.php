<?php
//start session
session_start();

$db_host = 'localhost';
$db_user = 'root'; 
$db_pass = ''; 
$db_name = 'numedev_personnel';

// Create database connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        // Verify password 
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
          
            header("Location: dashboard.php");
            exit();
        } else {
            // Invalid password
            $login_error = true;
        }
    } else {
        // Invalid email
        $login_error = true;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Numedev - Connexion</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="tailwind/tailwind.js"></script>
    <link rel="stylesheet" href="tailwind/tailwind.css">
    <link rel="shortcut icon" href="images/logoMirego.png" type="image/x-icon">
    <style>
        :root {
            --primary-color: #5D5CDE;
            --primary-hover: #4a49b1;
        }
        
        html{
            font-family: arial !important
        }
        .bg-primary {
            background-color: var(--primary-color);
        }
        
        .hover\:bg-primary-hover:hover {
            background-color: var(--primary-hover);
        }
        
        .text-primary {
            color: var(--primary-color);
        }
        
        .border-primary {
            border-color: var(--primary-color);
        }
        
        .focus\:ring-primary:focus {
            --tw-ring-color: var(--primary-color);
        }
        
        .focus\:border-primary:focus {
            border-color: var(--primary-color);
        }
        
        .login-container {
            transition: all 0.3s ease;
        }
        
        .form-input {
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .error-message {
            animation: fadeIn 0.3s ease-in-out;
        }
        
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
            }
            
            .login-image-container {
                width: 100%;
                max-width: 300px;
                margin: 0 auto 2rem auto;
            }
            
            .login-form-container {
                width: 100%;
            }
        }
    </style>
</head>
<body class="bg-gray-100 dark:bg-gray-900 min-h-screen flex items-center justify-center px-4 py-12">
    <div class="login-container bg-white dark:bg-gray-800 rounded-xl shadow-xl overflow-hidden max-w-4xl w-full flex">
       
        <div class="login-image-container w-1/2 bg-primary p-8 flex items-center justify-center">
<img src="images/login.svg" alt="">
        </div>
        
       
        <div class="login-form-container w-1/2 p-8">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-primary dark:text-white">Numedev</h1>
                <hr>
                <p class="text-gray-600 dark:text-gray-400 mt-2">Connectez-vous pour gérer le personnel</p>
            </div>
            
            <?php if (isset($login_error)): ?>
            <div class="error-message bg-red-100 border-l-4 border-red-500 text-red-700 p-4 dark:bg-red-900 dark:text-red-100 mb-6" role="alert">
                <p>Email ou mot de passe incorrect</p>
            </div>
            <?php endif; ?>
            
            <form action="<?php echo  $_SERVER['PHP_SELF']; ?>" method="POST" class="space-y-6">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                    <input type="email" id="email" name="email" 
                           class="form-input w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-700 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary text-base dark:bg-gray-700 dark:text-white transition-all" 
                           required>
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Mot de passe</label>
                    <input type="password" id="password" name="password" 
                           class="form-input w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-700 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary text-base dark:bg-gray-700 dark:text-white transition-all" 
                           required>
                </div>
                
                <button type="submit" name="login" 
                        class="w-full bg-primary hover:bg-primary-hover text-white py-3 px-4 rounded-lg font-medium transition-all duration-200 transform hover:-translate-y-1 shadow-md hover:shadow-lg">
                    Se connecter
                </button>
                
                <div class="bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-xl shadow-sm p-4 mt-6" role="alert">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-500 dark:text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-800 dark:text-blue-200">
                                Pour ce compte de démo: utilisez <span class="font-bold">admin@numedev.com</span> pour l'email et <span class="font-bold">numedev</span> pour le mot de passe
                            </p>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</body>
</html>