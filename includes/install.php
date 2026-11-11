<?php
try {
    // Users table
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT PRIMARY KEY AUTO_INCREMENT,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        email VARCHAR(100),
        phone VARCHAR(20),
        role ENUM('admin', 'user') DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Masters table
    $pdo->exec("CREATE TABLE IF NOT EXISTS masters (
        id INT PRIMARY KEY AUTO_INCREMENT,
        type VARCHAR(50) NOT NULL,
        code VARCHAR(50),
        name VARCHAR(200) NOT NULL,
        is_active BOOLEAN DEFAULT TRUE,
        sort_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Jobs table
    $stmt = $pdo->query("SHOW COLUMNS FROM jobs");
    $existingColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS jobs (
        id INT PRIMARY KEY AUTO_INCREMENT,
        job_number VARCHAR(50) UNIQUE NOT NULL,
        original_documents_date DATE,
        job_date DATE,
        bonded_carrier VARCHAR(200),
        do_collected_date DATE,
        customer_name VARCHAR(200),
        consignee VARCHAR(200),
        bl_number VARCHAR(100),
        container_number VARCHAR(100),
        container_size VARCHAR(20),
        khi_port VARCHAR(100),
        dry_port VARCHAR(100),
        port_of_loading VARCHAR(100),
        port_of_discharge VARCHAR(100),
        vessel_name VARCHAR(100),
        vessel_arrival_date DATE,
        shipping_line VARCHAR(200),
        freight_forwarder_name VARCHAR(200),
        depositor VARCHAR(200),
        free_time VARCHAR(50),
        detention_tariff VARCHAR(100),
        soc ENUM('Yes', 'No') DEFAULT 'No',
        mobile_no VARCHAR(20),
        description TEXT,
        remarks TEXT,
        status VARCHAR(20) DEFAULT 'active',
        created_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    
    $newColumns = [
        'original_documents_date' => "ALTER TABLE jobs ADD COLUMN original_documents_date DATE AFTER job_number",
        'bonded_carrier' => "ALTER TABLE jobs ADD COLUMN bonded_carrier VARCHAR(200) AFTER job_date",
        'do_collected_date' => "ALTER TABLE jobs ADD COLUMN do_collected_date DATE AFTER bonded_carrier",
        'customer_name' => "ALTER TABLE jobs ADD COLUMN customer_name VARCHAR(200) AFTER do_collected_date",
        'vessel_name' => "ALTER TABLE jobs ADD COLUMN vessel_name VARCHAR(100) AFTER port_of_discharge",
        'vessel_arrival_date' => "ALTER TABLE jobs ADD COLUMN vessel_arrival_date DATE AFTER vessel_name",
        'freight_forwarder_name' => "ALTER TABLE jobs ADD COLUMN freight_forwarder_name VARCHAR(200) AFTER shipping_line",
        'detention_tariff' => "ALTER TABLE jobs ADD COLUMN detention_tariff VARCHAR(100) AFTER free_time",
        'soc' => "ALTER TABLE jobs ADD COLUMN soc ENUM('Yes', 'No') DEFAULT 'No' AFTER detention_tariff",
        'mobile_no' => "ALTER TABLE jobs ADD COLUMN mobile_no VARCHAR(20) AFTER soc",
        'description' => "ALTER TABLE jobs ADD COLUMN description TEXT AFTER mobile_no"
    ];
    
    foreach ($newColumns as $colName => $alterSQL) {
        if (!in_array($colName, $existingColumns)) {
            try {
                $pdo->exec($alterSQL);
            } catch(PDOException $e) {}
        }
    }
    
    // Bills table
    $pdo->exec("CREATE TABLE IF NOT EXISTS bills (
        id INT PRIMARY KEY AUTO_INCREMENT,
        bill_number VARCHAR(100) UNIQUE NOT NULL,
        job_id INT NOT NULL,
        bill_type VARCHAR(50) DEFAULT 'forwarding',
        is_revised BOOLEAN DEFAULT FALSE,
        revision_number INT DEFAULT 0,
        parent_bill_id INT,
        total_amount DECIMAL(15,2) DEFAULT 0,
        advance_amount DECIMAL(15,2) DEFAULT 0,
        balance_amount DECIMAL(15,2) DEFAULT 0,
        balance_words VARCHAR(500),
        pdf_path VARCHAR(500),
        notes TEXT,
        created_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Bill items table
    $stmt = $pdo->query("SHOW COLUMNS FROM bill_items LIKE 'bill_type'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE bill_items ADD COLUMN bill_type VARCHAR(50) DEFAULT NULL");
    }
    
    $stmt = $pdo->query("SHOW COLUMNS FROM bill_items LIKE 'receipt_status'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE bill_items ADD COLUMN receipt_status VARCHAR(50) DEFAULT 'Unreceipted'");
    }
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS bill_items (
        id INT PRIMARY KEY AUTO_INCREMENT,
        bill_id INT NOT NULL,
        heading VARCHAR(255) NOT NULL,
        amount DECIMAL(15,2) DEFAULT 0,
        receipt_status VARCHAR(50) DEFAULT 'Unreceipted',
        bill_type VARCHAR(50) DEFAULT NULL,
        sort_order INT DEFAULT 0
    )");
    
    // User recent headings
    $pdo->exec("CREATE TABLE IF NOT EXISTS user_recent_headings (
        id INT PRIMARY KEY AUTO_INCREMENT,
        bill_type VARCHAR(50) NOT NULL,
        heading VARCHAR(255) NOT NULL,
        user_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_user_heading (bill_type, heading, user_id)
    )");
    
    // User job permissions
    $pdo->exec("CREATE TABLE IF NOT EXISTS user_job_permissions (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        job_id INT NOT NULL,
        granted_by INT NOT NULL,
        granted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_permission (user_id, job_id)
    )");
    
    // Karachi Clearance table
    $pdo->exec("CREATE TABLE IF NOT EXISTS karachi_clearance (
        id INT PRIMARY KEY AUTO_INCREMENT,
        karachi_clearance_number VARCHAR(50) UNIQUE NOT NULL,
        date DATE NOT NULL,
        shipper VARCHAR(200),
        on_account VARCHAR(200),
        clearing_agent VARCHAR(200),
        importer_name VARCHAR(200),
        bl_no VARCHAR(100),
        container_no VARCHAR(100),
        description TEXT,
        vessel_name VARCHAR(100),
        eta DATE,
        soc ENUM('Yes', 'No') DEFAULT 'No',
        status VARCHAR(20) DEFAULT 'active',
        created_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    
    // Loading Files table
    $pdo->exec("CREATE TABLE IF NOT EXISTS loading_files (
        id INT PRIMARY KEY AUTO_INCREMENT,
        job_id INT NOT NULL,
        clearing_agent VARCHAR(200),
        bonded_carrier VARCHAR(200),
        importer VARCHAR(200),
        dry_port VARCHAR(100),
        container_size VARCHAR(20),
        container_no VARCHAR(100),
        shipping_line VARCHAR(200),
        free_days VARCHAR(50),
        vessel_arrival DATE,
        loading_date DATE,
        lahore_arrival DATE,
        status VARCHAR(50) DEFAULT 'Pending',
        vehicles_no VARCHAR(100),
        trucker_cell VARCHAR(50),
        soc ENUM('Yes', 'No') DEFAULT 'No',
        created_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        table_date DATE,
        FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE
    )");
    
    // Create default admin user
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE username = 'admin'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $hashed = password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->prepare("INSERT INTO users (username, password, full_name, email, phone, role) VALUES ('admin', ?, 'Super Administrator', 'admin@qadria.com', '03001234567', 'admin')")->execute([$hashed]);
        $pdo->prepare("INSERT INTO users (username, password, full_name, email, phone, role) VALUES ('user', ?, 'Staff User', 'user@qadria.com', '03007654321', 'user')")->execute([$hashed]);
    }
    
    // Create default master data
    $stmt = $pdo->query("SELECT COUNT(*) FROM masters");
    if ($stmt->fetchColumn() == 0) {
        $masters = [
            ['bonded_carrier', 'BC001', 'Mahtab Sahab', 1],
            ['bonded_carrier', 'BC002', 'Lahore Bonded Warehouse', 2],
            ['bonded_carrier', 'BC003', 'Islamabad Bonded Zone', 3],
            ['do', 'DO001', 'Delivery Order - ABC', 1],
            ['do', 'DO002', 'Delivery Order - XYZ', 2],
            ['do', 'DO003', 'Delivery Order - 123', 3],
            ['customer_name', 'CUST001', 'Ghazi', 1],
            ['customer_name', 'CUST002', 'XYZ Customs Services', 2],
            ['customer_name', 'CUST003', 'Fast Track Clearing', 3],
            ['consignee', 'CON001', 'Al Tajjir Co Pvt Ltd', 1],
            ['consignee', 'CON002', 'ABC Traders A/C ASAD', 2],
            ['container_size', '20', '20 Feet', 1],
            ['container_size', '40', '40 Feet', 2],
            ['khi_port', 'KHI001', 'Karachi Port', 1],
            ['khi_port', 'KHI002', 'Port Qasim', 2],
            ['dry_port', 'DRY001', 'Lahore Dry Port', 1],
            ['dry_port', 'DRY002', 'Islamabad Dry Port', 2],
            ['vessel_name', 'VES001', 'MSC Cristina', 1],
            ['vessel_name', 'VES002', 'Maersk Hamburg', 2],
            ['shipping_line', 'SL001', 'MSC Shipping', 1],
            ['shipping_line', 'SL002', 'Maersk Line', 2],
            ['shipping_line', 'SL003', 'CMA CGM', 3],
            ['freight_forwarder_name', 'FF001', 'K Logistic', 1],
            ['freight_forwarder_name', 'FF002', 'Apex Shipping', 2],
            ['depositor', 'DEP001', 'ABC Bank', 1],
            ['depositor', 'DEP002', 'Allied Bank', 2],
        ];
        $insert = $pdo->prepare("INSERT INTO masters (type, code, name, sort_order) VALUES (?, ?, ?, ?)");
        foreach ($masters as $m) {
            $insert->execute($m);
        }
    }
} catch(PDOException $e) {}
?>