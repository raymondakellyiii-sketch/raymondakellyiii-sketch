<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

$logFile = __DIR__ . '/logs/game.log';
if (!is_dir(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs', 0775, true);
}

function log_event($message) {
    global $logFile;
    $user = $_SESSION['user'] ?? 'guest';
    $line = sprintf("[%s] (%s) %s\n", date('Y-m-d H:i:s'), $user, $message);
    file_put_contents($logFile, $line, FILE_APPEND);
}

if (!isset($_SESSION['craps'])) {
    $_SESSION['craps'] = [
        'point' => null,
        'history' => [],
        'status' => 'new'
    ];
}

$state = &$_SESSION['craps'];

//Actions handler
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'new') {
        $state = [
            'point' => null,
            'history' => [],
            'status' => 'new'
        ];
        // New game clear log
        file_put_contents($logFile, ""); 
        log_event("New game started.");
    }


    if ($action === 'roll' && ($state['status'] === 'new' || $state['status'] === 'running')) {
        $d1 = rand(1, 6);
        $d2 = rand(1, 6);
        $sum = $d1 + $d2;

        $state['history'][] = ['d1' => $d1, 'd2' => $d2, 'sum' => $sum];

        // Roll logic
        if ($state['point'] === null) {
            switch ($sum) {
                case 7:
                    $state['status'] = 'won';
                    log_event("First roll: $d1+$d2=$sum => Win");
                    break;
                case 11:
                    $state['status'] = 'won';
                    log_event("First roll: $d1+$d2=$sum => Win");
                    break;
                case 2:
                    $state['status'] = 'lost';
                    log_event("First roll: $d1+$d2=$sum => Lose");
                    break;
                case 3:
                    $state['status'] = 'lost';
                    log_event("Frist roll: $d1+$d2=$sum => Lose");
                    break;
                case 12:
                    $state['status'] = 'lost';
                    log_event("First roll: $d1+$d2=$sum => Lose");
                    break;
                default:
                    $state['point'] = $sum;
                    $state['status'] = 'running';
                    log_event("First roll: $d1+$d2=$sum => Point established: {$state['point']}");
            }
        } else {
            // Point phase: roll until point or seven
            if ($sum === $state['point']) {
                $state['status'] = 'won';
                log_event("Point phase: $d1+$d2=$sum matches point {$state['point']} => Win");
            } elseif ($sum === 7) {
                $state['status'] = 'lost';
                log_event("Point phase: $d1+$d2=$sum => Seven out => Lose");
            } else {
                // Roll again
                log_event("Point phase: $d1+$d2=$sum => Roll again...");
            }
        }
    }
}

function status_message($state) {
    if ($state['status'] === 'new') return 'Press Roll to start the game.';
    if ($state['status'] === 'running') return "Point is {$state['point']}. Roll until you hit the point (win) or 7 (lose).";
    if ($state['status'] === 'won') return 'You won! Start a new game to play again.';
    if ($state['status'] === 'lost') return 'You lost. Start a new game to try again.';
    return '';
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Craps</title>
<link rel="stylesheet" href="stylesheet.css">
</head>
<body>
<header>
    <div>Craps</div>
    <form method="post" action="logout.php">
        <button type="submit">Logout</button>
    </form>
</header>


<main class="container">
    <section>
        <h1>Craps</h1>
        <p><?php echo htmlspecialchars(status_message($state)); ?></p>

        <div>
            <form method="post">
                <?php if ($state['status'] === 'new' || $state['status'] === 'running'): ?>
                    <button name="action" value="roll">Roll</button>
                <?php endif; ?>
                <button name="action" value="new">New game</button>
            </form>
        </div>

        <div>
            <h2>Rules</h2>
            <ul>
                <li><strong>First roll win:</strong> 7 or 11</li>
                <li><strong>First roll lose:</strong> 2, 3, or 12</li>
                <li><strong>Otherwise:</strong> your roll becomes the point; keep rolling</li>
                <li><strong>Point phase:</strong> hit the point to win, 7 to lose</li>
            </ul>
        </div>

        <div>
            <h2>Roll history</h2>
            <?php if (empty($state['history'])): ?>
                <p>No rolls yet.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Die 1</th>
                            <th>Die 2</th>
                            <th>Sum</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($state['history'] as $i => $r): ?>
                        <tr>
                            <td><?php echo $i + 1; ?></td>
                            <td><?php echo $r['d1']; ?></td>
                            <td><?php echo $r['d2']; ?></td>
                            <td><?php echo $r['sum']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <?php if ($state['point'] !== null): ?>
        <div>
            <strong>Current point:</strong> <?php echo $state['point']; ?>
        </div>
        <?php endif; ?>

        <?php if ($state['status'] === 'won' || $state['status'] === 'lost'): ?>
        <div <?php echo $state['status'] === 'won' ? 'win' : 'lose'; ?>">
            <?php echo $state['status'] === 'won' ? 'WIN' : 'LOSE'; ?>
        </div>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
