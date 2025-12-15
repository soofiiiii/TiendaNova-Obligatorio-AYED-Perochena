<?php
namespace Application;

/**
 * Clase StockService
 *
 * Servicio de aplicación encargado de coordinar
 * operaciones relacionadas con el stock.
 *
 * Esta capa:
 * - Orquesta casos de uso
 * - Aplica reglas simples de flujo
 * - Delega la persistencia al repositorio
 *
 * No contiene lógica de presentación ni acceso directo a infraestructura.
 */
use Domain\Interfaces\IStockRepository;

final class StockService
{
    /**
     * El servicio depende de la abstracción IStockRepository,
     * no de una implementación concreta.
     *
     * Esto permite:
     * - Desacoplamiento
     * - Testeo más sencillo
     * - Cambiar la infraestructura sin afectar la lógica
     */
    public function __construct(private IStockRepository $repo) {}

    /**
     * Ajusta el stock de un producto aplicando un delta.
     *
     * El delta puede ser:
     * - Positivo (ingreso de stock)
     * - Negativo (egreso de stock)
     *
     * La lógica concreta de cómo se ajusta el stock
     * queda encapsulada en el repositorio.
     */
    public function ajustar(int $productoId, int $delta): void
    {
        $this->repo->ajustar($productoId, $delta);
    }

    /**
     * Actualiza completamente los datos del stock de un producto.
     *
     * Se utiliza cuando se edita:
     * - Cantidad
     * - Ubicación
     * - Stock mínimo
     */
    public function actualizar(int $productoId, int $cantidad, string $ubicacion, int $stockMinimo): void
    {
        $this->repo->update($productoId, $cantidad, $ubicacion, $stockMinimo);
    }

    /**
     * Devuelve todos los registros de stock que están
     * por debajo del mínimo configurado.
     *
     * Este método se usa típicamente para:
     * - Alertas
     * - Reportes
     * - Paneles de control
     *
     * @return \Domain\Entities\Stock[]
     */
    public function bajoMinimo(): array
    {
        return $this->repo->findBajoMinimo();
    }
}
