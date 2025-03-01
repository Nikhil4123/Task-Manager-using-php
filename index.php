<?php
$file = "tasks.txt";
$tasks = file_exists($file) ? file($file, FILE_IGNORE_NEW_LINES) : [];
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["task"]) && !empty($_POST["task"])) {
        $task = htmlspecialchars($_POST["task"]);
        $priority = isset($_POST["priority"]) ? (int)$_POST["priority"] : 2;
        $date = date("Y-m-d H:i:s");
        $tasks[] = json_encode([
            "text" => $task,
            "completed" => false,
            "priority" => $priority,
            "date" => $date
        ]);
    } elseif (isset($_POST["delete"])) {
        unset($tasks[$_POST["delete"]]);
        $tasks = array_values($tasks); 
    } elseif (isset($_POST["complete"])) {
        $taskData = json_decode($tasks[$_POST["complete"]], true);
        $taskData["completed"] = !$taskData["completed"]; 
        $tasks[$_POST["complete"]] = json_encode($taskData);
    } elseif (isset($_POST["clear_completed"])) {
        foreach ($tasks as $index => $task) {
            $taskData = json_decode($task, true);
            if ($taskData["completed"]) {
                unset($tasks[$index]);
            }
        }
        $tasks = array_values($tasks); 
    }
    
    file_put_contents($file, implode("\n", $tasks));
    header("Location: index.php");
    exit;
}
usort($tasks, function($a, $b) {
    $taskA = json_decode($a, true);
    $taskB = json_decode($b, true);
    

    if ($taskA["completed"] !== $taskB["completed"]) {
        return $taskA["completed"] ? 1 : -1;
    }
    
    return $taskB["priority"] - $taskA["priority"];
});
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enhanced To-Do List</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Task Manager</h1>
            <p id="date-display"><?= date("l, F j, Y") ?></p>
        </header>
        
        <div class="add-task-container">
            <form method="post" id="add-task-form">
                <div class="input-group">
                    <input type="text" name="task" id="task-input" placeholder="Add a new task..." required>
                    <select name="priority" id="priority-select">
                        <option value="3">High</option>
                        <option value="2" selected>Medium</option>
                        <option value="1">Low</option>
                    </select>
                    <button type="submit" id="add-button">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </form>
        </div>
        
        <div class="tasks-container">
            <div class="tasks-header">
                <h2>My Tasks <span class="counter"><?= count(array_filter($tasks, function($task) { 
                    return !json_decode($task, true)["completed"]; 
                })) ?></span></h2>
                
                <form method="post" style="display:inline;">
                    <input type="hidden" name="clear_completed" value="1">
                    <button type="submit" class="clear-btn">Clear Completed</button>
                </form>
            </div>
            
            <ul id="task-list">
                <?php foreach ($tasks as $index => $taskJson): 
                    $task = json_decode($taskJson, true);
                    $priorityClass = "priority-" . $task["priority"];
                    $completedClass = $task["completed"] ? "completed" : "";
                ?>
                    <li class="task-item <?= $priorityClass ?> <?= $completedClass ?>">
                        <div class="task-content">
                            <form method="post" class="complete-form">
                                <input type="hidden" name="complete" value="<?= $index ?>">
                                <button type="submit" class="complete-btn">
                                    <i class="<?= $task["completed"] ? 'fas fa-check-circle' : 'far fa-circle' ?>"></i>
                                </button>
                            </form>
                            
                            <div class="task-text">
                                <span class="task-title"><?= htmlspecialchars($task["text"]) ?></span>
                                <span class="task-date"><?= date("M j, g:i a", strtotime($task["date"])) ?></span>
                            </div>
                        </div>
                        
                        <form method="post" class="delete-form">
                            <input type="hidden" name="delete" value="<?= $index ?>">
                            <button type="submit" class="delete-btn">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </li>
                <?php endforeach; ?>
                
                <?php if (empty($tasks)): ?>
                    <li class="empty-list">
                        <i class="fas fa-clipboard-list"></i>
                        <p>Your task list is empty</p>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
        
        <footer>
            <p>Task Manager &copy; <?= date("Y") ?></p>
        </footer>
    </div>
    
    <script>
        document.getElementById('add-task-form').addEventListener('submit', function(e) {
            const taskInput = document.getElementById('task-input');
            if (taskInput.value.trim() === '') {
                e.preventDefault();
                taskInput.classList.add('error');
                setTimeout(() => {
                    taskInput.classList.remove('error');
                }, 1000);
            }
        });
    </script>
</body>
</html>