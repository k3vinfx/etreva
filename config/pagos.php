<?php
/*
 * config/pagos.php — Métodos de pago que se muestran al elegir un plan.
 *
 * ⚠ DATOS DE DEMOSTRACIÓN: las direcciones de billetera y el Binance Pay ID son de ejemplo y no
 *   pertenecen a nadie conocido. Antes de cobrar de verdad, reemplázalos por los de Etreva Travel.
 *   Lo que se envíe a una dirección sin dueño se pierde.
 *
 * El sistema NO cobra en línea ni emite facturas: el turista paga, adjunta la captura del comprobante
 * (en cripto también sirve el hash de la transacción) y el administrador lo valida. La factura, si
 * corresponde, se emite por fuera.
 */
return [
    // Tipo de cambio OFICIAL del Banco Central de Bolivia para mostrar el monto en bolivianos (los planes
    // están en USD). Es flexible desde junio de 2026: actualizarlo con el valor del día en www.bcb.gob.bo.
    // Bs 12,06 por dólar = valor oficial del BCB al 4 de noviembre de 2026.
    'tipo_cambio_bs'       => 12.06,
    'tipo_cambio_fecha'    => '2026-11-04',

    // Bolivia: el QR de Yape es interoperable (QR Simple), se puede pagar desde cualquier banco boliviano
    'yape' => [
        'activo'  => true,
        'titular' => 'Etreva Travel',
        'qr'      => 'assets/img/qr_yape.png',
    ],

    // Binance Pay: pago entre cuentas de Binance, sin comisión y sin elegir red
    'binance_pay' => [
        'activo' => true,
        'pay_id' => '721927942',              // ← DEMOSTRACIÓN: reemplazar por el Pay ID real
        'nombre' => 'Etreva Travel',
    ],

    // Cripto estable (1 USDT/USDC = 1 USD). Las redes EVM comparten la misma dirección.
    'cripto' => [
        'activo'    => true,
        'billetera_evm'  => '0x077389a6a880ae664aeba51ecf27a151594c87ed',   // ← DEMOSTRACIÓN (BEP-20, Polygon, ERC-20)
        'billetera_tron' => 'TCx2SYrtnHcRbkjcJKhUQ5oU4v8vwnSBW6',           // ← DEMOSTRACIÓN (TRC-20)
        // clave => [nombre, cadena, tipo de dirección, explorador para ver la transacción, comisión aprox., monedas]
        'redes' => [
            'trc20'   => ['TRC-20',  'Tron',             'tron', 'https://tronscan.org/#/transaction/', 'baja',  ['USDT']],
            'bep20'   => ['BEP-20',  'BNB Smart Chain',  'evm',  'https://bscscan.com/tx/',              'baja',  ['USDT', 'USDC']],
            'polygon' => ['Polygon', 'Polygon PoS',      'evm',  'https://polygonscan.com/tx/',          'baja',  ['USDT', 'USDC']],
            'erc20'   => ['ERC-20',  'Ethereum',         'evm',  'https://etherscan.io/tx/',             'alta',  ['USDT', 'USDC']],
        ],
    ],
];
