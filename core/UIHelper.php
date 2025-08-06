<?php
/**
 * STACK Core - UI Helper
 * Dynamic CRUD Management System
 * 
 * @author Skryper
 * @website https://stack.ge/
 * @version 1.0.0
 */

class UIHelper {
    
    /**
     * Generate page header
     */
    public static function getHeader($title = '', $additionalCSS = '') {
        $config = getAppConfig();
        $pageTitle = $title ? $title . ' - ' . $config['name'] : $config['name'];
        
        return '<!DOCTYPE html>
<html lang="ka">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . htmlspecialchars($pageTitle) . '</title>
    <meta name="description" content="' . htmlspecialchars($config['description']) . '">
    <meta name="author" content="' . htmlspecialchars($config['author']) . '">
    
    <!-- CSS Framework -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --stack-primary: #667eea;
            --stack-secondary: #764ba2;
            --stack-success: #06d6a0;
            --stack-info: #118ab2;
            --stack-warning: #ffd166;
            --stack-danger: #ef476f;
            --stack-dark: #073b4c;
            --stack-light: #f8f9fa;
        }
        
        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        
        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
        }
        
        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            transition: all 0.2s ease-in-out;
        }
        
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        
        .table-card {
            cursor: pointer;
            border-left: 4px solid var(--stack-primary);
        }
        
        .stats-card {
            background: linear-gradient(135deg, var(--stack-primary) 0%, var(--stack-secondary) 100%);
            color: white;
            border: none;
        }
        
        .btn-stack {
            background: linear-gradient(135deg, var(--stack-primary) 0%, var(--stack-secondary) 100%);
            border: none;
            color: white;
        }
        
        .btn-stack:hover {
            background: linear-gradient(135deg, var(--stack-secondary) 0%, var(--stack-primary) 100%);
            color: white;
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--stack-dark) 0%, #0a4b5e 100%) !important;
            box-shadow: 0 2px 4px rgba(0,0,0,.1);
        }
        
        .hero-section {
            background: linear-gradient(135deg, var(--stack-primary) 0%, var(--stack-secondary) 100%);
            color: white;
            padding: 3rem 0;
            margin-bottom: 2rem;
        }
        
        .footer {
            background: var(--stack-dark);
            color: white;
            padding: 2rem 0;
            margin-top: 3rem;
        }
        
        .alert {
            border: none;
            border-radius: 0.5rem;
        }
        
        .table {
            background: white;
            border-radius: 0.5rem;
            overflow: hidden;
        }
        
        .table thead th {
            background: var(--stack-dark);
            color: white;
            border: none;
            font-weight: 600;
        }
        
        .pagination .page-link {
            color: var(--stack-primary);
            border: 1px solid #dee2e6;
        }
        
        .pagination .page-item.active .page-link {
            background: var(--stack-primary);
            border-color: var(--stack-primary);
        }
        
        .form-control:focus {
            border-color: var(--stack-primary);
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .loading-spinner {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 9999;
        }
        
        .column-row {
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1rem;
            background: var(--stack-light);
            transition: all 0.2s;
        }
        
        .column-row:hover {
            background: #e9ecef;
        }
        
        .remove-column {
            background: var(--stack-danger);
            border: none;
            color: white;
            border-radius: 50%;
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .action-buttons .btn {
            margin: 2px;
            padding: 0.25rem 0.5rem;
        }
        
        @media (max-width: 768px) {
            .hero-section {
                padding: 2rem 0;
            }
            
            .table-responsive {
                font-size: 0.875rem;
            }
            
            .action-buttons {
                display: flex;
                flex-direction: column;
            }
        }
    </style>
    ' . $additionalCSS . '
</head>
<body>';
    }
    
    /**
     * Generate navigation bar
     */
    public static function getNavbar($currentPage = '') {
        $config = getAppConfig();
        
        // Get the current directory context for relative paths
        $basePath = '';
        if (strpos($_SERVER['REQUEST_URI'], '/public/') !== false) {
            $basePath = '';
        } elseif (strpos($_SERVER['REQUEST_URI'], '/views/') !== false) {
            $basePath = '../public/';
        } else {
            $basePath = 'public/';
        }
        
        return '
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="' . $basePath . 'index.php">
                <i class="fas fa-database me-2"></i>' . $config['name'] . '
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link' . ($currentPage === 'index' ? ' active' : '') . '" href="' . $basePath . 'index.php">
                            <i class="fas fa-home me-1"></i> მთავარი
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link' . ($currentPage === 'tables' ? ' active' : '') . '" href="' . $basePath . 'tables.php">
                            <i class="fas fa-table me-1"></i> ცხრილები
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link' . ($currentPage === 'create' ? ' active' : '') . '" href="' . $basePath . 'create.php">
                            <i class="fas fa-plus me-1"></i> ახალი ცხრილი
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-cog me-1"></i> სისტემა
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="showSystemInfo()">
                                <i class="fas fa-info-circle me-1"></i> სისტემის ინფო
                            </a></li>
                            <li><a class="dropdown-item" href="' . $config['website'] . '" target="_blank">
                                <i class="fas fa-external-link-alt me-1"></i> ვებსაიტი
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><span class="dropdown-item-text text-muted small">
                                ' . $config['version'] . ' ' . $config['author'] . '
                            </span></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>';
    }
    
    /**
     * Generate footer
     */
    public static function getFooter() {
        $config = getAppConfig();
        
        return '
    <!-- Footer -->
    <footer class="footer mt-auto">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>' . $config['name'] . '</h5>
                    <p class="mb-0">' . $config['description'] . '</p>
                    <small class="text-muted">Version ' . $config['version'] . '</small>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">' . $config['author'] . '</p>
                    <a href="' . $config['website'] . '" target="_blank" class="text-light">
                        <i class="fas fa-external-link-alt me-1"></i>' . $config['website'] . '
                    </a>
                    <br>
                    <small class="text-muted">&copy; ' . date('Y') . ' All rights reserved.</small>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Global STACK Core functions
        function showSystemInfo() {
            Swal.fire({
                title: "' . $config['name'] . '",
                html: `
                    <div class="text-start">
                        <p><strong>Version:</strong> ' . $config['version'] . '</p>
                        <p><strong>Author:</strong> ' . $config['author'] . '</p>
                        <p><strong>Website:</strong> <a href="' . $config['website'] . '" target="_blank">' . $config['website'] . '</a></p>
                        <p><strong>Description:</strong> ' . $config['description'] . '</p>
                        <hr>
                        <p><strong>Database:</strong> ' . (isset($_SESSION['db_connected']) && $_SESSION['db_connected'] ? 'Connected' : 'Disconnected') . '</p>
                        ' . (isset($_SESSION['db_info']) ? '<p><strong>Host:</strong> ' . $_SESSION['db_info']['host'] . '</p>' : '') . '
                        ' . (isset($_SESSION['db_info']) ? '<p><strong>Database:</strong> ' . $_SESSION['db_info']['database'] . '</p>' : '') . '
                    </div>
                `,
                icon: "info",
                confirmButtonColor: "#667eea"
            });
        }
        
        function showLoading() {
            Swal.fire({
                title: "ლოდება...",
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        }
        
        function hideLoading() {
            Swal.close();
        }
        
        function showSuccess(message) {
            Swal.fire({
                title: "წარმატება!",
                text: message,
                icon: "success",
                confirmButtonColor: "#06d6a0"
            });
        }
        
        function showError(message) {
            Swal.fire({
                title: "შეცდომა!",
                text: message,
                icon: "error",
                confirmButtonColor: "#ef476f"
            });
        }
        
        function confirmAction(message, callback) {
            Swal.fire({
                title: "დარწმუნებული ხართ?",
                text: message,
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#ef476f",
                cancelButtonColor: "#6c757d",
                confirmButtonText: "დიახ, გაგრძელება",
                cancelButtonText: "გაუქმება"
            }).then((result) => {
                if (result.isConfirmed && callback) {
                    callback();
                }
            });
        }
    </script>
</body>
</html>';
    }
    
    /**
     * Generate alert message
     */
    public static function showAlert($message, $type = 'info', $dismissible = true) {
        $alertClass = 'alert-' . $type;
        $icon = [
            'success' => 'check-circle',
            'danger' => 'exclamation-triangle',
            'warning' => 'exclamation-circle',
            'info' => 'info-circle'
        ][$type] ?? 'info-circle';
        
        $dismissButton = $dismissible ? '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' : '';
        
        return '<div class="alert ' . $alertClass . ' alert-dismissible fade show" role="alert">
            <i class="fas fa-' . $icon . ' me-2"></i>' . htmlspecialchars($message) . $dismissButton . '
        </div>';
    }
    
    /**
     * Generate pagination
     */
    public static function getPagination($currentPage, $totalPages, $baseUrl, $params = []) {
        if ($totalPages <= 1) return '';
        
        $pagination = '<nav aria-label="Page navigation"><ul class="pagination justify-content-center">';
        
        // Previous button
        if ($currentPage > 1) {
            $prevParams = array_merge($params, ['page' => $currentPage - 1]);
            $pagination .= '<li class="page-item">
                <a class="page-link" href="' . $baseUrl . '?' . http_build_query($prevParams) . '">
                    <i class="fas fa-chevron-left"></i> წინა
                </a>
            </li>';
        }
        
        // Page numbers
        $startPage = max(1, $currentPage - 2);
        $endPage = min($totalPages, $currentPage + 2);
        
        if ($startPage > 1) {
            $firstParams = array_merge($params, ['page' => 1]);
            $pagination .= '<li class="page-item">
                <a class="page-link" href="' . $baseUrl . '?' . http_build_query($firstParams) . '">1</a>
            </li>';
            if ($startPage > 2) {
                $pagination .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
        }
        
        for ($i = $startPage; $i <= $endPage; $i++) {
            $pageParams = array_merge($params, ['page' => $i]);
            $active = $i === $currentPage ? ' active' : '';
            $pagination .= '<li class="page-item' . $active . '">
                <a class="page-link" href="' . $baseUrl . '?' . http_build_query($pageParams) . '">' . $i . '</a>
            </li>';
        }
        
        if ($endPage < $totalPages) {
            if ($endPage < $totalPages - 1) {
                $pagination .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
            $lastParams = array_merge($params, ['page' => $totalPages]);
            $pagination .= '<li class="page-item">
                <a class="page-link" href="' . $baseUrl . '?' . http_build_query($lastParams) . '">' . $totalPages . '</a>
            </li>';
        }
        
        // Next button
        if ($currentPage < $totalPages) {
            $nextParams = array_merge($params, ['page' => $currentPage + 1]);
            $pagination .= '<li class="page-item">
                <a class="page-link" href="' . $baseUrl . '?' . http_build_query($nextParams) . '">
                    შემდეგი <i class="fas fa-chevron-right"></i>
                </a>
            </li>';
        }
        
        $pagination .= '</ul></nav>';
        
        return $pagination;
    }
    
    /**
     * Format file size
     */
    public static function formatFileSize($bytes) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }
    
    /**
     * Truncate text
     */
    public static function truncateText($text, $limit = 50, $suffix = '...') {
        if (mb_strlen($text) <= $limit) {
            return $text;
        }
        return mb_substr($text, 0, $limit) . $suffix;
    }
}
?>
