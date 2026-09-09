<?php
session_start();

// Initialize score counter in session if not set
if (!isset($_SESSION['player_score'])) {
    $_SESSION['player_score'] = 0;
    $_SESSION['computer_score'] = 0;
    $_SESSION['ties'] = 0;
}

// Handle Reset Request
if (isset($_POST['reset'])) {
    $_SESSION['player_score'] = 0;
    $_SESSION['computer_score'] = 0;
    $_SESSION['ties'] = 0;
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Setting variables for computer and player choices
$choices = ['Rock', 'Paper', 'Scissors'];
$userChoice = '';
$computerChoice = '';
$resultMessage = '';


//-------------------------------------------------------------------------------------------
// Track score BEFORE the current turn for the display
$displayPlayerScore = $_SESSION['player_score'];
$displayComputerScore = $_SESSION['computer_score'];
$displayTies = $_SESSION['ties'];
//-------------------------------------------------------------------------------------------

// Process game logic on submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_choice'])) {
    $userChoice = $_POST['user_choice'];
    
    if (in_array($userChoice, $choices)) {
        // Computer selects a random choice
        $computerChoice = $choices[array_rand($choices)];

        // Game logic
        if ($userChoice === $computerChoice) {
            $resultMessage = "It's a Tie!";
            $_SESSION['ties']++;
        } elseif (
            ($userChoice === 'Rock' && $computerChoice === 'Scissors') ||
            ($userChoice === 'Paper' && $computerChoice === 'Rock') ||
            ($userChoice === 'Scissors' && $computerChoice === 'Paper')
        ) {
            $resultMessage = "You Win! $userChoice beats $computerChoice.";
            $_SESSION['player_score']++;
        } else {
            $resultMessage = "Computer Wins! $computerChoice beats $userChoice.";
            $_SESSION['computer_score']++;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Rock Paper Scissors</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; margin-top: 50px; background-color: #f4f4f9; }
        .card { background: white; max-width: 400px; margin: 0 auto; padding: 20px; border-radius: 20px; box-shadow: 10px 20px 55px rgba(0,0,0,0.5); }
        .slot-screen { font-size: 1.5rem; font-weight: bold; margin: 20px 0; min-height: 50px; color: #0366d6;}
        .result-box { font-size: 1.2rem; font-weight: bold; margin-top: 15px; color: #28a745; min-height: 30px; }
        table { margin: 20px auto 0; border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: center; }
        th { background-color: #007bff; color: white; }
        button, select { padding: 10px 15px; font-size: 1rem; margin-top: 10px; cursor: pointer; }
    </style>
</head>
<body>

<div class="card">
    <h2>Rock Paper Scissors</h2>

    <form method="POST" id="gameForm" onsubmit="disableGoBtn();">
        <label for="user_choice">Choose your move:</label><br>
        <select name="user_choice" id="user_choice" required>
            <option value="Rock" <?php if ($userChoice === 'Rock') echo 'selected'; ?>>Rock</option>
            <option value="Paper" <?php if ($userChoice === 'Paper') echo 'selected'; ?>>Paper</option>
            <option value="Scissors" <?php if ($userChoice === 'Scissors') echo 'selected'; ?>>Scissors</option>
        </select>
        <br>
        <button type="submit" id="goBtn">Go!</button>
    </form>

    <div class="slot-screen" id="slotScreen">
        <?php echo $computerChoice ? $computerChoice : 'Computer choice: ???'; ?>
    </div>

    <div class="result-box" id="resultBox"></div>

    <table>
        <tr>
            <th>Player</th>
            <th>Computer</th>
            <th>Ties</th>
        </tr>
        <tr>
            <!-- ----------------------------------------------------------------------------- -->
            <!-- Displays score BEFORE the round until JS finishes the slot animation -->
            <td id="playerScore"><?php echo $displayPlayerScore; ?></td>
            <td id="computerScore"><?php echo $displayComputerScore; ?></td>
            <td id="tiesScore"><?php echo $displayTies; ?></td>
            <!-- ----------------------------------------------------------------------------- -->
        </tr>
    </table>

    <form method="POST">
        <button type="submit" name="reset" style="background-color: #dc3545; color: white; border: none; border-radius: 4px;">Reset Score</button>
    </form>
</div>

<?php if (!empty($computerChoice)): ?>
    <script>
        const choices = <?php echo json_encode($choices); ?>;
        const finalChoice = "<?php echo $computerChoice; ?>";
        const resultText = "<?php echo $resultMessage; ?>";
        
        //------------------------------------------------------------------------------------
        // Pass the updated scores from PHP session to JavaScript variables
        const newPlayerScore = <?php echo $_SESSION['player_score']; ?>;
        const newComputerScore = <?php echo $_SESSION['computer_score']; ?>;
        const newTiesScore = <?php echo $_SESSION['ties']; ?>;
        //------------------------------------------------------------------------------------

        const slotScreen = document.getElementById('slotScreen');
        const resultBox = document.getElementById('resultBox');
        const playerScore = document.getElementById('playerScore');
        const computerScore = document.getElementById('computerScore');
        const tiesScore = document.getElementById('tiesScore');
        
        // Disabling Go button during the slot-screen-random-choice-animation and re-enable it back at the end of cycleChoices() functoin.....
        const goBtn = document.getElementById('goBtn');
        goBtn.disabled = true;

        let counter = 0;
        let speed = 50; 
        let totalRounds = 20; 

        function cycleChoices() {
            slotScreen.innerText = choices[counter % choices.length];
            counter++;

            if (counter < totalRounds) {
                speed += 15; 
                setTimeout(cycleChoices, speed);
            } else {
                // Lock in final choices and display text result
                slotScreen.innerText = finalChoice;
                resultBox.innerText = resultText;

                //------------------------------------------------------------------------------------
                // Update the scoreboard table ONLY when the animation completes
                playerScore.innerText = newPlayerScore;
                computerScore.innerText = newComputerScore;
                tiesScore.innerText = newTiesScore;
                //------------------------------------------------------------------------------------

                // Enabling the Go button when the slot-screen-random-choice-animation ends
                goBtn.disabled = false;
            }
        }

        cycleChoices();
    </script>
<?php endif; ?>

</body>
</html>
