CREATE TABLE esports_registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,

    -- Player Details
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    contact VARCHAR(20) NOT NULL,
    country VARCHAR(50) NOT NULL,

    -- Game Details
    game_uid VARCHAR(50),
    in_game_name VARCHAR(100),
    current_level INT,

    -- Tournament Options
    tournament_type VARCHAR(50) NOT NULL,
    game_name VARCHAR(50) NOT NULL,
    additional_info TEXT,

    -- Payment Details
    payment_id VARCHAR(100),
    payment_name VARCHAR(100),
    payment_remark VARCHAR(255),
    payment_proof_image VARCHAR(255), -- Store image filename or relative path

    -- Metadata
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
