<?php

declare(strict_types=1);
final class CoursesController
{
    public function index(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        if (!isset($_SESSION['auth_user'])) {
            header('Location: login');
            exit;
        }
        $db = databaseConnection();
        $userId = (int)$_SESSION['auth_user']['id'];
        (new AuthorizationService($db))->require((int)$_SESSION['auth_user']['id'], 'cursos.ver');
        if (empty($_SESSION['course_csrf'])) $_SESSION['course_csrf'] = bin2hex(random_bytes(32));
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!hash_equals((string)$_SESSION['course_csrf'], (string)($_POST['_token'] ?? ''))) {
                http_response_code(419);
                exit('Token inválido.');
            }
            if (in_array($_POST['action'] ?? '', ['activate', 'deactivate'], true)) {
                $courseId = (int)($_POST['course_id'] ?? 0);
                (new Course($db))->setStatus($courseId, $_POST['action'] === 'activate' ? 'activo' : 'inactivo');
                (new AuditService($db))->log($userId, $_POST['action'] === 'activate' ? 'Activar curso' : 'Desactivar curso', 'cursos', $courseId);
                header('Location: cursos');
                exit;
            }
            $courseId = (int)($_POST['course_id'] ?? 0);
            $codigo = trim((string)($_POST['codigo'] ?? ''));
            if ($courseId === 0 && $codigo === '') $codigo = (string)(((int)$db->query('SELECT COALESCE(MAX(CAST(codigo AS UNSIGNED)), 0) FROM cursos')->fetchColumn()) + 1);
            $exists = $db->prepare('SELECT COUNT(*) FROM cursos WHERE codigo=:codigo AND id<>:id AND deleted_at IS NULL');
            $exists->execute(['codigo' => $codigo, 'id' => $courseId]);
            if ((int)$exists->fetchColumn() > 0) {
                if ($courseId === 0) $codigo = (string)(((int)$db->query('SELECT COALESCE(MAX(CAST(codigo AS UNSIGNED)), 0) FROM cursos')->fetchColumn()) + 1);
                else {
                    $_SESSION['course_error'] = 'El código del curso ya existe.';
                    header('Location: cursos');
                    exit;
                }
            }
            $courseData = ['codigo' => $codigo, 'nombre' => strtoupper(trim((string)$_POST['nombre'])), 'descripcion' => strtoupper(trim((string)($_POST['descripcion'] ?? ''))) ?: null, 'horas' => (int)($_POST['horas_academicas'] ?? 0), 'inicio' => $_POST['fecha_inicio'] ?: null, 'fin' => $_POST['fecha_fin'] ?: null];
            if ($courseId > 0) {
                (new Course($db))->update($courseId, $courseData);
                (new AuditService($db))->log($userId, 'Editar curso', 'cursos', $courseId);
            } else {
                $courseId = (new Course($db))->create($courseData);
                (new AuditService($db))->log($userId, 'Crear curso', 'cursos', $courseId);
            }
            header('Location: cursos');
            exit;
        }
        $courses = (new Course($db))->all();
        $nextCourseCode = (string)(((int)$db->query('SELECT COALESCE(MAX(CAST(codigo AS UNSIGNED)), 0) FROM cursos')->fetchColumn()) + 1);
        ob_start();
        require dirname(__DIR__) . '/Views/courses/index.php';
        $pageContent = ob_get_clean();
        $pageTitle = 'Cursos | Sistema de Certificados';
        $displayName = 'Wilmer Coscol';
        $roleName = (string)($_SESSION['auth_user']['role'] ?? '');
        require dirname(__DIR__) . '/Views/layouts/dashboard.php';
    }
}
