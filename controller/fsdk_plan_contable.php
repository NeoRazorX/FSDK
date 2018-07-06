<?php
/*
 * This file is part of FacturaScripts
 * Copyright (C) 2017-2018  Carlos Garcia Gomez  neorazorx@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Description of fsdk_plan_contable
 *
 * @author carlos
 */
class fsdk_plan_contable extends fs_controller
{

    public $codejercicio;
    public $ejercicio;
    public $separador;
    private $cuenta;
    private $epigrafe;
    private $subcuenta;
    private $ultima_cuenta;
    private $ultimo_epigrafe;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Plan contable', 'admin', FALSE, FALSE);
    }

    protected function private_core()
    {
        if (class_exists('subcuenta')) {
            $this->codejercicio = FALSE;
            $this->cuenta = new cuenta();
            $this->ejercicio = new ejercicio();
            $this->epigrafe = new epigrafe();
            $this->separador = ';';
            $this->subcuenta = new subcuenta();
            $this->ultima_cuenta = NULL;
            $this->ultimo_epigrafe = NULL;

            if (isset($_POST['csv'])) {
                $this->codejercicio = $_POST['codejercicio'];
                $this->separador = $_POST['separador'];

                if (is_uploaded_file($_FILES['fcsv']['tmp_name'])) {
                    $this->procesar_csv();
                } else {
                    $this->new_error_msg('Error al subir el archivo.');
                }
            }
        } else {
            $this->new_message('Activa el plugin facturacion_base.');
        }
    }

    private function procesar_csv()
    {
        $longcuentas = 0;
        $longsubcuentas = 0;
        $plinea = FALSE;

        $fcsv = fopen($_FILES['fcsv']['tmp_name'], 'r');
        if ($fcsv) {
            while (!feof($fcsv)) {
                $aux = trim(fgets($fcsv));
                if ($aux != '') {
                    $linea = explode($this->separador, $aux);

                    if (isset($linea[0])) {
                        $linea[0] = str_replace('.', '', trim($linea[0]));
                    }

                    if (isset($linea[1])) {
                        $linea[1] = trim($linea[1]);
                        if (mb_detect_encoding($linea[1], 'UTF-8', TRUE) === FALSE) {
                            /// si no es utf8, convertimos
                            $linea[1] = utf8_encode($linea[1]);
                        }
                    }

                    if ($plinea) {
                        if (strlen($linea[0]) > $longsubcuentas) {
                            $longcuentas = $longsubcuentas;
                            $longsubcuentas = strlen($linea[0]);
                        }
                    } else if ($linea[0] == 'CODIGO' AND $linea[1] == 'NOMBRE') {
                        $plinea = TRUE;
                    }
                }
            }

            /// rebobinamos
            rewind($fcsv);

            if ($plinea) {
                $this->new_message('Se ha detectado que las cuentas tienen una longitud de '
                    . $longcuentas . ', y las subcuentas una longitud de ' . $longsubcuentas . '.');

                $continuar = TRUE;
                $num_epigrafes = 0;
                $num_cuentas = 0;
                $num_subcuentas = 0;
                while (!feof($fcsv)) {
                    $aux = trim(fgets($fcsv));
                    if ($aux != '') {
                        $linea = explode($this->separador, $aux);

                        if (isset($linea[0])) {
                            $linea[0] = str_replace('.', '', trim($linea[0]));
                        }

                        if (isset($linea[1])) {
                            $linea[1] = trim($linea[1]);
                            if (mb_detect_encoding($linea[1], 'UTF-8', TRUE) === FALSE) {
                                /// si no es utf8, convertimos
                                $linea[1] = utf8_encode($linea[1]);
                            }
                        }

                        if ($linea[0] != 'CODIGO' AND $linea[1] != 'NOMBRE') {
                            /// ahora procesamos los datos en función de las longitudes de los códigos
                            switch (strlen($linea[0])) {
                                case 0:
                                    /// nada
                                    break;

                                case $longcuentas;
                                    $continuar = $this->crear_cuenta($linea);
                                    $num_cuentas++;
                                    break;

                                case $longsubcuentas:
                                    $continuar = $this->crear_subcuenta($linea);
                                    $num_subcuentas++;
                                    break;

                                default:
                                    $continuar = $this->crear_epigrafe($linea);
                                    $num_epigrafes++;
                                    break;
                            }

                            if (!$continuar) {
                                break;
                            }
                        }
                    }
                }

                if ($continuar) {
                    $this->new_message('Proceso terminado: ' . $num_epigrafes . ' epígrafes, ' . $num_cuentas . ' cuentas y ' . $num_subcuentas . ' subcuentas creadas.');
                }
            } else {
                $this->new_error_msg('El archivo debe tener las columnas CODIGO y NOMBRE.');
            }

            fclose($fcsv);
        }
    }

    private function crear_epigrafe(&$linea)
    {
        /// ¿Existe ya el epigrafe?
        $epi = $this->epigrafe->get_by_codigo($linea[0], $this->codejercicio);
        if ($epi) {
            /// ya existe
            $this->ultimo_epigrafe = $epi;
            return TRUE;
        } else {
            /// no existe, lo creamos
            $epi = new epigrafe();
            $epi->codejercicio = $this->codejercicio;
            $epi->codepigrafe = $linea[0];
            $epi->descripcion = $linea[1];

            /// usamos ultimo_epigrafe para ahorrar coprobaciones
            if ($this->ultimo_epigrafe) {
                if (strlen($this->ultimo_epigrafe->codepigrafe) >= strlen($epi->codepigrafe)) {
                    /// pero si el nuevo no tiene mayor longitud, mejor descartamos para buscar uno nuevo
                    $this->ultimo_epigrafe = NULL;
                }
            }

            if (!$this->ultimo_epigrafe AND strlen($linea[0]) > 1) {
                /// buscamos un padre
                $this->ultimo_epigrafe = $this->epigrafe->get_by_codigo(substr($epi->codepigrafe, 0, -1), $this->codejercicio);
            }

            if (!$this->ultimo_epigrafe AND strlen($linea[0]) > 2) {
                /// buscamos un padre
                $this->ultimo_epigrafe = $this->epigrafe->get_by_codigo(substr($epi->codepigrafe, 0, -2), $this->codejercicio);
            }

            if (!$this->ultimo_epigrafe AND strlen($linea[0]) > 3) {
                /// buscamos un padre
                $this->ultimo_epigrafe = $this->epigrafe->get_by_codigo(substr($epi->codepigrafe, 0, -3), $this->codejercicio);
            }

            if ($this->ultimo_epigrafe) {
                if (strlen($this->ultimo_epigrafe->codepigrafe) < strlen($epi->codepigrafe)) {
                    /// asignamos el padre
                    $epi->idpadre = $this->ultimo_epigrafe->idepigrafe;
                }
            }

            if ($epi->save()) {
                $this->ultimo_epigrafe = $epi;
                return TRUE;
            } else {
                $this->new_error_msg('Error al procesar el epigrafe ' . $epi->codepigrafe);
                return FALSE;
            }
        }
    }

    private function crear_cuenta(&$linea)
    {
        /// ¿Existe ya la cuenta?
        $cuenta = $this->cuenta->get_by_codigo($linea[0], $this->codejercicio);
        if ($cuenta) {
            $this->ultima_cuenta = $cuenta;
            return TRUE;
        } else if ($this->ultimo_epigrafe) {
            /// creamos la cuenta
            $cuenta = new cuenta();
            $cuenta->codcuenta = $linea[0];
            $cuenta->codejercicio = $this->codejercicio;
            $cuenta->codepigrafe = $this->ultimo_epigrafe->codepigrafe;
            $cuenta->descripcion = $linea[1];
            $cuenta->idepigrafe = $this->ultimo_epigrafe->idepigrafe;
            if ($cuenta->save()) {
                $this->ultima_cuenta = $cuenta;
                return TRUE;
            } else {
                $this->new_error_msg('Error al crear la cuenta ' . $linea[0]);
                return FALSE;
            }
        } else {
            $this->new_error_msg('Epígrafe no encontrado para la cuenta ' . $linea[0]);
            return FALSE;
        }
    }

    private function crear_subcuenta(&$linea)
    {
        /// ¿Existe ya la subcuenta?
        $subcuenta = $this->subcuenta->get_by_codigo($linea[0], $this->codejercicio);
        if ($subcuenta) {
            return TRUE;
        } else if ($this->ultima_cuenta) {
            $subcuenta = new subcuenta();
            $subcuenta->codcuenta = $this->ultima_cuenta->codcuenta;
            $subcuenta->coddivisa = $this->empresa->coddivisa;
            $subcuenta->codejercicio = $this->codejercicio;
            $subcuenta->codsubcuenta = $linea[0];
            $subcuenta->descripcion = $linea[1];
            $subcuenta->idcuenta = $this->ultima_cuenta->idcuenta;
            if ($subcuenta->save()) {
                return TRUE;
            } else {
                $this->new_error_msg('Error al crear la subcuenta ' . $linea[0]);
                return FALSE;
            }
        } else {
            $this->new_error_msg('Cuenta no encontrada para la subcuenta ' . $linea[0]);
            return FALSE;
        }
    }
}
