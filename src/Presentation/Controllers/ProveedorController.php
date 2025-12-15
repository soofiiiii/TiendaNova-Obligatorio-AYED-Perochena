<?php
namespace Presentation\Controllers;


use Application\ProveedorService;
use Infrastructure\Repositories\PdoProveedorRepository;
use Core\ViewRenderer;
use Presentation\Helpers\Validator;

final class ProveedorController
{
    private ProveedorService $svc;

    public function __construct()
    {
        $this->svc = new ProveedorService(new PdoProveedorRepository());
    }

    public function listar(): void
    {
        $proveedores = array_map(function ($p) {
            return [
                'id' => $p->getId(),
                'nombre' => $p->getNombreEmpresa(),
                'email' => $p->getEmail(),
                'telefono' => $p->getTelefono(),
                'productos' => $this->svc->contarProductosAsociados($p->getId())
            ];
        }, $this->svc->listarTodos());

        ViewRenderer::render('proveedor/listar', [
            'active' => 'proveedores',
            'entidad' => 'proveedor',
            'ruta' => 'proveedor',
            'columnas' => ['ID', 'Nombre', 'Email', 'Teléfono', 'Productos'],
            'campos' => ['id', 'nombre', 'email', 'telefono', 'productos'],
            'items' => $proveedores
        ]);
    }



    public function crearForm(): void
    {
        ViewRenderer::render('proveedor/crear', [
            'active' => 'proveedores',
        ]);
    }

    public function crear(): void
    {
        Validator::requireFields($_POST, ['nombre_empresa', 'contacto', 'telefono', 'email', 'direccion']);

        $dto = [
            'nombre_empresa' => trim($_POST['nombre_empresa']),
            'contacto' => trim($_POST['contacto']),
            'telefono' => trim($_POST['telefono']),
            'email' => trim($_POST['email']),
            'direccion' => trim($_POST['direccion']),
        ];

        $this->svc->crear($dto);

        header('Location: ' . BASE_PATH . '/proveedores');
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

        $prov = $this->svc->obtenerPorId($id);
        if (!$prov) {
            http_response_code(404);
            echo "Proveedor no encontrado";
            return;
        }

        ViewRenderer::render('proveedor/editar', [
            'prov' => $prov,
            'active' => 'proveedores',
        ]);
    }

    public function editar(): void
    {
        Validator::requireFields($_POST, ['id', 'nombre_empresa', 'contacto', 'telefono', 'email', 'direccion']);

        $id = (int) $_POST['id'];
        $dto = [
            'nombre_empresa' => trim($_POST['nombre_empresa']),
            'contacto' => trim($_POST['contacto']),
            'telefono' => trim($_POST['telefono']),
            'email' => trim($_POST['email']),
            'direccion' => trim($_POST['direccion']),
        ];

        $this->svc->actualizar($id, $dto);

        header('Location: ' . BASE_PATH . '/proveedores');
        exit;
    }

    public function eliminar(): void
    {
        $id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

        if ($id <= 0) {
            header('Location: ' . BASE_PATH . '/proveedores');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            if (isset($_POST['confirm']) && $_POST['confirm'] === '1') {
                try {
                    $this->svc->eliminar($id);
                } catch (\DomainException $e) {
                    $_SESSION['error_eliminar_proveedor'] = $e->getMessage();

                    header('Location: ' . BASE_PATH . "/proveedores/eliminar?id={$id}");
                    exit;
                } catch (\Throwable $t) {
                    $_SESSION['error_eliminar_proveedor'] =
                        'No se pudo eliminar el proveedor debido a productos asociados.';

                    header('Location: ' . BASE_PATH . "/proveedores/eliminar?id={$id}");
                    exit;
                }
            }

            header('Location: ' . BASE_PATH . '/proveedores');
            exit;
        }

        // GET → cargar proveedor
        $proveedor = $this->svc->obtenerPorId($id);
        if (!$proveedor) {
            header('Location: ' . BASE_PATH . '/proveedores');
            exit;
        }

        // (Opcional) Cantidad de productos asociados para mostrar en la vista
        $cantidadProductos = $this->svc->contarProductosAsociados($id) ?? null;

        ViewRenderer::render('proveedor/eliminar', [
            'active' => 'proveedores',
            'proveedor' => $proveedor,
            'cantidadProductos' => $cantidadProductos,
            'error' => $_SESSION['error_eliminar_proveedor'] ?? null
        ]);

        unset($_SESSION['error_eliminar_proveedor']);
    }

}