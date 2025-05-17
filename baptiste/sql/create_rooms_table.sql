CREATE TABLE IF NOT EXISTS rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(6) UNIQUE NOT NULL,
    player1_id INT,
    player2_id INT,
    player1_score INT DEFAULT 0,
    player2_score INT DEFAULT 0,
    current_question INT DEFAULT 1,
    game_status ENUM('waiting', 'in_progress', 'finished') DEFAULT 'waiting',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (player1_id) REFERENCES users(id),
    FOREIGN KEY (player2_id) REFERENCES users(id)
);