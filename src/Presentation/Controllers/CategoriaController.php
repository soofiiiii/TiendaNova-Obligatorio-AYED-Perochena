<?php
namespace Presentation\Controllers;

use Application\CategoriaService;
use Infrastructure\Repositories\PdoCategoriaRepository;
use Core\ViewRenderer;
use Presentation\Utils\Validator;

final class CategoriaController
{
    private CategoriaService $svc;

    public function __construct()
    {
        // En una aplicación real, el repo y el svc se inyectan, pero para el ejemplo se instancia aquí.
        $this->svc = new CategoriaService(new PdoCategoriaRepository());
    }

    public function listar(): void
    {
        // 1. Obtener el árbol completo usando el servicio
        $roots = $this->svc->obtenerArbolCompleto();

        // 2. Enviar a la vista también el servicio ($svc) porque la vista necesita llmar a contarProductoEnRama()
        ViewRenderer::render('categoria/listar', [
            'active' => 'categorias',
            'entidad' => 'categoría',
            'ruta' => 'categorias',

            // columnas exactas que usa la vista
            'columnas' => ['ID', 'Nombre', 'Descirpción', 'Nivel', 'Productos Totales'],

            // pasar el árbol real
            'roots' => $roots,

            // pasar el servicio para contar productos 
            'svc' => $this->svc
        ]);
    }

    public function crearForm(): void
    {
        $roots = $this->svc->obtenerArbolCompleto();
        ViewRenderer::render('categoria/crear', [
            'roots' => $roots,
            'active' => 'categorias',
        ]);
    }
    public function crear(): void
    {
        $errors = Validator::required($_POST, ['nombre']);

        if ($errors) {
            echo Validator::errorsToHtml($errors);
            return;
        }

        $nombre = trim($_POST['nombre']);
        $descripcion = $_POST['descripcion'] !== '' ? $_POST['descripcion'] : null;
        $parentId = $_POST['parent_id'] !== '' ? (int) $_POST['parent_id'] : null;

        $this->svc->crear($nombre, $descripcion, $parentId);

        header('Location: ' . BASE_PATH . '/categorias');
        exit;
    }

    public function editarForm(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo "ID inválido";
            return;
        }

        $cat = $this->svc->obtenerPorId($id);
        if (!$cat) {
            http_response_code(404);
            echo "Categoría no encontrada";
            return;
        }

        $roots = $this->svc->obtenerArbolCompleto();

        ViewRenderer::render('categoria/editar', [
            'cat' => $cat,
            'roots' => $roots,
            'active' => 'categorias',
        ]);
    }

    public function editar(): void
    {
        $errors = Validator::required($_POST, ['nombre']);

        if ($errors) {
            echo Validator::errorsToHtml($errors);
            return;
        }

        $id = (int) $_POST['id'];
        $nombre = trim($_POST['nombre']);
        $descripcion = $_POST['descripcion'] !== '' ? $_POST['descripcion'] : null;
        $parentId = $_POST['parent_id'] !== '' ? (int) $_POST['parent_id'] : null;

        $this->svc->actualizar($id, $nombre, $descripcion, $parentId);

        header('Location: ' . BASE_PATH . '/categorias');
        exit;
    }

    public function eliminar(): void
    {

        // Obtener ID desde GET o POST
        $id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

        // Si no viene ID, volvemos a la lista
        if ($id <= 0) {
            header('Location: ' . BASE_PATH . '/categorias');
            exit;
        }

        // Si llega por POST → intento eliminar
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            // Debe venir confirmación explícita
            if (isset($_POST['confirm']) && $_POST['confirm'] === '1') {
                try {
                    $this->svc->eliminar($id);
                } catch (\DomainException $e) {
                    // (Opcional) Guardar error en sesión
                    $_SESSION['error_eliminar_categoria'] = $e->getMessage();

                    // Redirigir nuevamente a la vista de confirmación
                    header('Location: ' . BASE_PATH . "/categorias/eliminar?id={$id}");
                    exit;
                } catch (\Throwable $t) {
                    // Error inesperado (FKs, SQL, etc.)
                    $_SESSION['error_eliminar_categoria'] =
                        'No se pudo eliminar la categoría debido a dependencias existentes.';

                    header('Location: ' . BASE_PATH . "/categorias/eliminar?id={$id}");
                    exit;
                }
            }

            // Eliminación exitosa → volver al listado
            header('Location: ' . BASE_PATH . '/categorias');
            exit;
        }

        // GET → mostrar vista de confirmación

        // Obtener categoría real desde el servicio
        $categoria = $this->svc->obtenerPorId($id);
        if (!$categoria) {
            header('Location: ' . BASE_PATH . '/categorias');
            exit;
        }

        // (Opcional) Cantidades para mostrar info poderosa en la vista
        $totalProductos = $this->svc->contarProductosEnRama($id) ?? null;
        $totalSubcategorias = $this->svc->contarSubcategoriasDirectas($id) ?? null;

        ViewRenderer::render('categoria/eliminar', [
            'active' => 'categorias',
            'categoria' => $categoria,
            'totalProductos' => $totalProductos,
            'totalSubcategorias' => $totalSubcategorias,
            'error' => $_SESSION['error_eliminar_categoria'] ?? null
        ]);

        unset($_SESSION['error_eliminar_categoria']);
    }


    public function moverForm(): void
    {
        $roots = $this->svc->obtenerArbolCompleto();
        ViewRenderer::render('categoria/mover', [
            'tree' => $roots,
            'active' => 'categorias',
        ]);
    }

    public function mover(): void
    {
        $errors = Validator::required($_POST, ['nombre']);

        if ($errors) {
            echo Validator::errorsToHtml($errors);
            return;
        }

        $id = (int) $_POST['categoria_id'];
        $nuevoParentId = $_POST['nuevo_parent_id'] !== '' ? (int) $_POST['nuevo_parent_id'] : null;

        $this->svc->mover($id, $nuevoParentId);

        header('Location: ' . BASE_PATH . '/categorias');
        exit;
    }
}