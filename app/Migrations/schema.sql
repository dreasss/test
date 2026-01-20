CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role ENUM('admin','agent','user') NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    phone VARCHAR(50) NULL,
    department VARCHAR(190) NULL,
    building VARCHAR(50) NULL,
    room VARCHAR(50) NULL,
    locale VARCHAR(10) NOT NULL DEFAULT 'ru',
    avatar_url VARCHAR(255) NULL,
    sso_subject VARCHAR(190) NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    author_id INT NOT NULL,
    assignee_id INT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    priority ENUM('high','medium','low') NOT NULL DEFAULT 'low',
    desired_at DATETIME NULL,
    status ENUM('new','assigned','in_progress','waiting_user','resolved','closed','reopened') NOT NULL DEFAULT 'new',
    building VARCHAR(50) NULL,
    room VARCHAR(50) NULL,
    attachments JSON NULL,
    last_agent_reply_at DATETIME NULL,
    last_user_reply_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id),
    FOREIGN KEY (assignee_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT NOT NULL,
    author_id INT NOT NULL,
    body TEXT NOT NULL,
    attachments JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_system TINYINT(1) NOT NULL DEFAULT 0,
    FOREIGN KEY (ticket_id) REFERENCES tickets(id),
    FOREIGN KEY (author_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS knowledge_articles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(190) NOT NULL UNIQUE,
    title_ru VARCHAR(255) NOT NULL,
    body_ru TEXT NOT NULL,
    title_en VARCHAR(255) NOT NULL,
    body_en TEXT NOT NULL,
    tags VARCHAR(255) NULL,
    created_by INT NOT NULL,
    updated_by INT NOT NULL,
    status ENUM('draft','published') NOT NULL DEFAULT 'draft',
    usefulness_up INT NOT NULL DEFAULT 0,
    usefulness_down INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (updated_by) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS news_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title_ru VARCHAR(255) NOT NULL,
    body_ru TEXT NOT NULL,
    title_en VARCHAR(255) NOT NULL,
    body_en TEXT NOT NULL,
    cover_url VARCHAR(255) NULL,
    publish_at DATETIME NOT NULL,
    is_poll TINYINT(1) NOT NULL DEFAULT 0,
    poll_options JSON NULL,
    poll_votes JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS branding (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name_ru VARCHAR(190) NOT NULL,
    name_en VARCHAR(190) NOT NULL,
    slogan_ru VARCHAR(255) NOT NULL,
    slogan_en VARCHAR(255) NOT NULL,
    logo_url VARCHAR(255) NULL,
    color_primary VARCHAR(20) NOT NULL,
    color_secondary VARCHAR(20) NOT NULL
);

CREATE TABLE IF NOT EXISTS sync_queue (
    id INT AUTO_INCREMENT PRIMARY KEY,
    entity_type VARCHAR(50) NOT NULL,
    entity_id INT NOT NULL,
    action VARCHAR(50) NOT NULL,
    payload JSON NOT NULL,
    attempt_count INT NOT NULL DEFAULT 0,
    last_error TEXT NULL,
    status ENUM('pending','done','failed') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
