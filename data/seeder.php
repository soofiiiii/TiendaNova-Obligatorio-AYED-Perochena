<?php
require __DIR__ . '/../config/bootstrap.php';

use Infrastructure\Repositories\PdoCategoriaRepository;
use Infrastructure\Repositories\PdoProveedorRepository;
use Infrastructure\Repositories\PdoProductoRepository;
use Infrastructure\Repositories\PdoStockRepository;

$catRepo  = new PdoCategoriaRepository();
$provRepo = new PdoProveedorRepository();
$stockRepo= new PdoStockRepository();
$prodRepo = new PdoProductoRepository($stockRepo);


// Categorías 
$rootElec = $catRepo->create('Electrónica', 'Tecnología y gadgets', null);
$cel      = $catRepo->create('Celulares', null, $rootElec);
$comp     = $catRepo->create('Computadoras', null, $rootElec);
$laptops  = $catRepo->create('Laptops', null, $comp);
$desktop  = $catRepo->create('Desktop', null, $comp);
$audio    = $catRepo->create('Audio', null, $rootElec);

$ropa     = $catRepo->create('Ropa', null, null);
$hombre   = $catRepo->create('Hombre', null, $ropa);
$mujer    = $catRepo->create('Mujer', null, $ropa);

$hogar    = $catRepo->create('Hogar', null, null);
$cocina   = $catRepo->create('Cocina', null, $hogar);
$deco     = $catRepo->create('Decoración', null, $hogar);

// Proveedores
$p1 = $provRepo->create('TechWorld SA', 'Ana Torres', '099123123', 'ventas@techworld.com', 'Av. Central 123');
$p2 = $provRepo->create('ElectroPlus SRL', 'Marcos Díaz', '098111222', 'mdiaz@electroplus.io', 'Ituzaingó 456');
$p3 = $provRepo->create('Casa Hogar SA', 'Lucía Pérez', '097888777', 'lp@casahogar.com', 'Río Negro 321');


// Productos
$ids = [
  ['Teléfono X1', $cel, 299.99, $p1],
  ['Auriculares Pro', $audio, 79.90, $p2],
  ['Notebook Nova 14', $laptops, 799.00, $p1],
  ['PC Escritorio Alpha', $desktop, 599.00, $p2],
  ['Batidora Max', $cocina, 59.90, $p3],
  ['Tostadora Lite', $cocina, 39.90, $p3],
  ['Camisa Oxford', $hombre, 24.50, $p2],
  ['Pantalón Slim', $hombre, 34.90, $p2],
  ['Vestido Summer', $mujer, 44.90, $p2],
  ['Lámpara Deco', $deco, 19.90, $p3],
];

foreach ($ids as $row) {
    [$nombre, $catId, $precio, $provId] = $row;
    $prodId = $prodRepo->create($nombre, null, $precio, $catId, $provId);
    $stockRepo->create($prodId, rand(5,30), 'DEP-1', 3);
}

echo "Seed OK\n";