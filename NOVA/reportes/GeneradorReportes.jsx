import React, { useEffect, useState } from "react";
import DashboardReporteNova from "./DashboardReporteNova";
import {
    FaFileExcel,
    FaFileAlt,
    FaDownload,
    FaSyncAlt,
    FaSlidersH,
    FaInfoCircle,
} from "react-icons/fa";

import {
    getReportesNova,
    getParametrosReporteNova,
    getValoresParametroNova,
    generarReporteNova,
} from "../../models/reportesService";

export default function GeneradorReportes() {
    const [reportes, setReportes] = useState([]);
    const [reporteSeleccionado, setReporteSeleccionado] = useState("");
    const [reporteActual, setReporteActual] = useState(null);

    const [parametros, setParametros] = useState([]);
    const [valoresParametros, setValoresParametros] = useState({});
    const [filtros, setFiltros] = useState({});

    const [cargando, setCargando] = useState(false);
    const [cargandoParametros, setCargandoParametros] = useState(false);

    const [mensaje, setMensaje] = useState("");

    const [cargandoReporte, setCargandoReporte] = useState(false);
    const [resultadoReporte, setResultadoReporte] = useState(null);
    const [vistaDashboard, setVistaDashboard] = useState(false);

    useEffect(() => {
        cargarReportes();
    }, []);

    const cargarReportes = async () => {
        setCargando(true);
        setMensaje("");

        try {
            const res = await getReportesNova();

            if (!res.error) {

                // SOLO REPORTES ACTIVOS
                const reportesActivos = (res.data || []).filter(
                    (reporte) => reporte.est_rpt === "a"
                );

                setReportes(reportesActivos);

                if (reportesActivos.length === 0) {
                    setMensaje(
                        "No hay reportes activos disponibles para generar."
                    );
                }

            } else {
                setReportes([]);
                setMensaje("No fue posible cargar los reportes.");
            }

        } catch (error) {
            setReportes([]);
            setMensaje("Ocurrió un error al cargar los reportes.");
        } finally {
            setCargando(false);
        }
    };

    const seleccionarReporte = async (id) => {
        setReporteSeleccionado(id);
        setMensaje("");

        const rpt = reportes.find((x) => String(x.id_rpt) === String(id));

        setReporteActual(rpt || null);
        setParametros([]);
        setValoresParametros({});
        setFiltros({});

        if (!rpt) {
            setMensaje("");
            return;
        }

        setCargandoParametros(true);

        try {
            const res = await getParametrosReporteNova(rpt.id_rpt);

            if (!res.error) {
                const parametrosActivos = (res.data || []).filter(
                    (p) => p.est_rpar === "a"
                );

                setParametros(parametrosActivos);

                const filtrosIniciales = {};
                const valoresTemp = {};
                
                for (const parametro of parametrosActivos) {
                    filtrosIniciales[parametro.id_rpar] = "";

                    const resValores = await getValoresParametroNova(parametro.id_rpar);

                    if (!resValores.error) {
                        valoresTemp[parametro.id_rpar] = resValores.data || [];
                    }
                }

                setFiltros(filtrosIniciales);
                setValoresParametros(valoresTemp);

                if (parametrosActivos.length === 0) {
                    setMensaje(
                        "Este reporte todavía no tiene parámetros configurados."
                    );
                }
            } else {
                setMensaje("No fue posible cargar los parámetros del reporte.");
            }
        } catch (error) {
            setMensaje("Ocurrió un error al cargar los parámetros.");
        } finally {
            setCargandoParametros(false);
        }
    };

    const cambiarFiltro = (idParametro, valor) => {
        setFiltros((prev) => ({
            ...prev,
            [idParametro]: valor,
        }));
    };

    const generarReporte = async () => {
        if (!reporteActual) {
            alert("Seleccione un reporte.");
            return;
        }

        setCargandoReporte(true);
        setMensaje("");

        try {
            const filtrosFinales = {};

            parametros.forEach((parametro) => {
                const valor = filtros[parametro.id_rpar] || null;
                const valores = valoresParametros[parametro.id_rpar] || [];

                if (valores.length > 0 && valores[0].var_rpv) {
                    filtrosFinales[valores[0].var_rpv] = valor;
                }
            });

            const res = await generarReporteNova(
                reporteActual.id_rpt,
                filtrosFinales
            );

            if (res.error) {
                setMensaje(res.message || "No fue posible generar el reporte.");
                return;
            }

            setResultadoReporte(res);
            setVistaDashboard(true);

        } catch (error) {
            setMensaje("Ocurrió un error al generar el reporte.");
        } finally {
            setCargandoReporte(false);
        }
    };

    const renderFiltro = (parametro) => {
        const tipo = parametro.tip_rpar;
        const valor = filtros[parametro.id_rpar] || "";

        const claseInput =
            "h-11 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm text-slate-700 outline-none transition focus:border-sky-500 focus:ring-4 focus:ring-sky-100";

        if (tipo === "select") {
            return (
                <select
                    value={valor}
                    onChange={(e) =>
                        cambiarFiltro(parametro.id_rpar, e.target.value)
                    }
                    className={claseInput}
                >
                    <option value="">[Seleccione]</option>

                    {(valoresParametros[parametro.id_rpar] || []).map(
                        (item) => (
                            <option key={item.id_rpv} value={item.val_rpv}>
                                {item.tit_rpv || item.val_rpv}
                            </option>
                        )
                    )}
                </select>
            );
        }

        if (tipo === "date") {
            return (
                <input
                    type="date"
                    value={valor}
                    onChange={(e) =>
                        cambiarFiltro(parametro.id_rpar, e.target.value)
                    }
                    className={claseInput}
                />
            );
        }

        if (tipo === "number") {
            return (
                <input
                    type="number"
                    value={valor}
                    onChange={(e) =>
                        cambiarFiltro(parametro.id_rpar, e.target.value)
                    }
                    className={claseInput}
                    placeholder="Ingrese un valor numérico"
                />
            );
        }

        return (
            <input
                type="text"
                value={valor}
                onChange={(e) =>
                    cambiarFiltro(parametro.id_rpar, e.target.value)
                }
                className={claseInput}
                placeholder="Ingrese un valor"
            />
        );
    };

    const puedeGenerar = Boolean(reporteActual);

    if (vistaDashboard && resultadoReporte) {
        return (
            <DashboardReporteNova
                resultado={resultadoReporte}
                onVolver={() => setVistaDashboard(false)}
            />
        );
    }

    return (
        <div className="min-h-screen w-full bg-slate-100 px-4 py-6 md:px-8">
            <div className="mx-auto w-full max-w-7xl">
                <div className="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-lg">
                    <div className="border-b border-sky-100 bg-gradient-to-r from-sky-50 via-white to-emerald-50 px-6 py-5">
                        <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                            <div className="flex items-center gap-4">
                                <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-sky-100 text-sky-700 shadow-sm ring-1 ring-sky-200">
                                    <FaFileExcel className="text-xl" />
                                </div>

                                <div>
                                    <h1 className="text-xl font-bold text-slate-800">
                                        Generador de Reportes
                                    </h1>
                                    <p className="text-sm text-slate-500">
                                        Nova SISA - Reportes configurables
                                    </p>
                                </div>
                            </div>

                            <button
                                onClick={cargarReportes}
                                disabled={cargando}
                                className="flex items-center justify-center gap-2 rounded-xl bg-sky-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-700 disabled:cursor-not-allowed disabled:opacity-60"
                            >
                                <FaSyncAlt
                                    className={cargando ? "animate-spin" : ""}
                                />
                                Actualizar
                            </button>
                        </div>
                    </div>

                    <div className="grid grid-cols-1 gap-6 p-6 lg:grid-cols-3">
                        <div className="lg:col-span-1">
                            <div className="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                                <label className="mb-2 flex items-center gap-2 text-sm font-bold text-slate-700">
                                    <FaFileAlt className="text-sky-600" />
                                    Seleccionar reporte
                                </label>

                                <select
                                    value={reporteSeleccionado}
                                    onChange={(e) =>
                                        seleccionarReporte(e.target.value)
                                    }
                                    className="h-11 w-full rounded-xl border border-slate-300 bg-white px-4 text-sm text-slate-700 outline-none transition focus:border-sky-500 focus:ring-4 focus:ring-sky-100"
                                >
                                    <option value="">[SELECCIONE]</option>

                                    {reportes.map((rpt) => (
                                        <option
                                            key={rpt.id_rpt}
                                            value={rpt.id_rpt}
                                        >
                                            {rpt.des_rpt}
                                        </option>
                                    ))}
                                </select>

                                <div className="mt-4 rounded-xl border border-slate-200 bg-white p-4">
                                    <h3 className="mb-2 text-sm font-bold text-slate-700">
                                        Estado del reporte
                                    </h3>

                                    {reporteActual ? (
                                        <div className="space-y-2 text-sm text-slate-600">
                                            <p>
                                                <span className="font-semibold">
                                                    Reporte:
                                                </span>{" "}
                                                {reporteActual.des_rpt}
                                            </p>
                                            <p>
                                                <span className="font-semibold">
                                                    Parámetros:
                                                </span>{" "}
                                                {parametros.length}
                                            </p>
                                            <p>
                                                <span className="font-semibold">
                                                    Generación:
                                                </span>{" "}
                                                Pendiente por configuración
                                            </p>
                                        </div>
                                    ) : (
                                        <p className="text-sm text-slate-500">
                                            Seleccione un reporte para visualizar
                                            sus filtros.
                                        </p>
                                    )}
                                </div>
                            </div>
                        </div>

                        <div className="lg:col-span-2">
                            <div className="rounded-2xl border border-slate-200 bg-white p-5">
                                <div className="mb-5 flex items-center justify-between border-b border-slate-100 pb-4">
                                    <div>
                                        <h2 className="flex items-center gap-2 text-base font-bold text-slate-800">
                                            <FaSlidersH className="text-sky-600" />
                                            Parámetros del reporte
                                        </h2>
                                        <p className="text-sm text-slate-500">
                                            Los filtros se cargan según el
                                            reporte seleccionado.
                                        </p>
                                    </div>
                                </div>

                                {cargandoParametros && (
                                    <div className="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-500">
                                        Cargando parámetros del reporte...
                                    </div>
                                )}

                                {!reporteActual && !cargandoParametros && (
                                    <div className="rounded-xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm text-slate-500">
                                        No hay un reporte seleccionado.
                                    </div>
                                )}

                                {reporteActual &&
                                    !cargandoParametros &&
                                    parametros.length > 0 && (
                                        <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                            {parametros.map((parametro) => (
                                                <div key={parametro.id_rpar}>
                                                    <label className="mb-1 block text-sm font-semibold text-slate-600">
                                                        {parametro.des_rpar}
                                                    </label>

                                                    {renderFiltro(parametro)}
                                                </div>
                                            ))}
                                        </div>
                                    )}

                                {reporteActual &&
                                    !cargandoParametros &&
                                    parametros.length === 0 && (
                                        <div className="rounded-xl border border-amber-200 bg-amber-50 px-4 py-4 text-sm text-amber-700">
                                            Este reporte todavía no tiene
                                            parámetros configurados. Primero
                                            debe registrar los parámetros en la
                                            base de datos.
                                        </div>
                                    )}

                                {mensaje && (
                                    <div className="mt-4 flex gap-2 rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-700">
                                        <FaInfoCircle className="mt-0.5" />
                                        <span>{mensaje}</span>
                                    </div>
                                )}

                                <div className="mt-6 flex justify-end">
                                    <button
                                        onClick={generarReporte}
                                        disabled={!puedeGenerar || cargandoReporte}
                                        className="flex items-center gap-2 rounded-xl bg-sky-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-700 disabled:cursor-not-allowed disabled:bg-slate-300"
                                    >
                                        <FaDownload />
                                        {cargandoReporte ? "Generando..." : "Generar Reporte"}
                                    </button>
                                </div>
                            </div>

                            <div className="mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-5">
                                <h3 className="mb-2 text-sm font-bold text-slate-700">
                                    Vista previa
                                </h3>

                                <div className="rounded-xl border border-dashed border-slate-300 bg-white px-4 py-5 text-sm text-slate-600">
                                    {reporteActual ? (
                                        <div className="space-y-2">
                                            <p>
                                                <span className="font-bold">Reporte:</span>{" "}
                                                {reporteActual.des_rpt}
                                            </p>

                                            <p>
                                                <span className="font-bold">Destino:</span>{" "}
                                                {reporteActual.destino || "No configurado"}
                                            </p>

                                            <p>
                                                <span className="font-bold">Tipo de gráfica:</span>{" "}
                                                {reporteActual.tp_gr || "No configurado"}
                                            </p>

                                            <p>
                                                <span className="font-bold">Eje X:</span>{" "}
                                                {reporteActual.eje_x || "No configurado"}
                                            </p>

                                            <p>
                                                <span className="font-bold">Eje Y:</span>{" "}
                                                {reporteActual.eje_y || "No configurado"}
                                            </p>

                                            <div className="pt-3">
                                                <p className="mb-2 font-bold">Filtros seleccionados:</p>

                                                {parametros.map((p) => (
                                                    <p key={p.id_rpar}>
                                                        {p.des_rpar}:{" "}
                                                        <span className="font-semibold">
                                                            {filtros[p.id_rpar] || "Sin seleccionar"}
                                                        </span>
                                                    </p>
                                                ))}
                                            </div>
                                        </div>
                                    ) : (
                                        <p className="text-center text-slate-500">
                                            Seleccione un reporte para ver la vista previa.
                                        </p>
                                    )}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}