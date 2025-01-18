<?php
function findShortestPath($maze, $start, $end) {
    $rows = count($maze);
    $cols = count($maze[0]);

    if ($maze[$start[0]][$start[1]] === 0 || $maze[$end[0]][$end[1]] === 0) {
        throw new InvalidArgumentException("В стартовую и конечную точку невозможно попасть, так как она 0.");
    }

    $directions = [[-1, 0], [1, 0], [0, -1], [0, 1]]; 
    $distances = array_fill(0, $rows, array_fill(0, $cols, PHP_INT_MAX));
    $previous = array_fill(0, $rows, array_fill(0, $cols, null));
    $visited = array_fill(0, $rows, array_fill(0, $cols, false));

    $distances[$start[0]][$start[1]] = 0;

    $queue = new SplPriorityQueue();
    $queue->setExtractFlags(SplPriorityQueue::EXTR_BOTH);
    $queue->insert($start, 0);

    while (!$queue->isEmpty()) {
        $current = $queue->extract();
        $priority = -$current['priority'];
        [$x, $y] = $current['data'];

        if ($visited[$x][$y]) continue;
        $visited[$x][$y] = true;

        foreach ($directions as [$dx, $dy]) {
            $newX = $x + $dx;
            $newY = $y + $dy;

            if (
                $newX >= 0 && $newX < $rows &&
                $newY >= 0 && $newY < $cols &&
                $maze[$newX][$newY] !== 0
            ) {
                $newDist = $distances[$x][$y] + 1;
                if ($newDist < $distances[$newX][$newY]) {
                    $distances[$newX][$newY] = $newDist;
                    $previous[$newX][$newY] = [$x, $y];
                    $queue->insert([$newX, $newY], -$newDist);
                }
            }
        }
    }

    $path = [];
    $current = $end;

    while ($current !== null) {
        $path[] = $current;
        $current = $previous[$current[0]][$current[1]] ?? null;
    }

    return array_reverse($path);
}

function readInput() {
    fscanf(STDIN, "%d %d\n", $rows, $cols);

    if ($rows <= 0 || $cols <= 0) {
        throw new InvalidArgumentException("Длина и ширина лабиринта должны быть положительными числами.");
    }

    $maze = [];
    for ($i = 0; $i < $rows; $i++) {
        $line = trim(fgets(STDIN));
        $row = array_map('intval', explode(' ', $line));

        if (count($row) !== $cols) {
            throw new InvalidArgumentException("Неверный ввод.");
        }

        $maze[] = $row;
    }

    fscanf(STDIN, "%d %d %d %d\n", $startX, $startY, $endX, $endY);

    if (
        $startX < 0 || $startX >= $rows ||
        $startY < 0 || $startY >= $cols ||
        $endX < 0 || $endX >= $rows ||
        $endY < 0 || $endY >= $cols
    ) {
        throw new InvalidArgumentException("Выход за пределы лабиринта.");
    }

    return [$maze, [$startX, $startY], [$endX, $endY]];
}

try {
    [$maze, $start, $end] = readInput();
    $path = findShortestPath($maze, $start, $end);

    if (!empty($path) && $path[0] === $start) {
        foreach ($path as [$x, $y]) {
            echo "$x $y\n";
        }
        echo ".\n";
    } else {
        echo "Такой путь не найден.\n";
    }
} catch (Exception $e) {
    fwrite(STDERR, "Error: " . $e->getMessage() . "\n");
    exit(1);
}
