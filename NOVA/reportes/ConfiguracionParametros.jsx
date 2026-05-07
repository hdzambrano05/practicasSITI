import React, { useEffect, useState } from "react";
import { Helmet } from "react-helmet";
import {
    FaChartLine,
    FaFileAlt,
    FaEdit,
    FaSlidersH,
    FaSearch,
    FaSyncAlt,
    FaTrash,
    FaPlus,
    FaSave,
    FaList,
} from "react-icons/fa";

import {
    getReportesNova,
    guardarReporteNova,
    actualizarDestinoReporte,
    actualizarEstadoReporte,
    actualizarReporteNova,
    getParametrosReporteNova,
    guardarParametroReporteNova,
    actualizarParametroReporteNova,
    eliminarParametroReporteNova,
    getValoresParametroNova,
    guardarValorParametroNova,
    actualizarValorParametroNova,
    eliminarValorParametroNova,
} from "../../models/reportesService";

export default function ConfiguracionParametros() {
    const [reportes, setReportes] = useState([]);
    const [busqueda, setBusqueda] = useState("");
    const [cargando, setCargando] = useState(false);

    const [modalNuevoReporte, setModalNuevoReporte] = useState(false);

    const [formReporte, setFormReporte] = useState({
        des_rpt: "",
        sql_rpt: "",
        est_rpt: "a",
        destino: "i",
        id_men: "",
        id_ano: "",
        id_pla: "",
        difusion: "",
    });

    const [modalEditar, setModalEditar] = useState(false);
    const [reporteEditando, setReporteEditando] = useState(null);

    const [modalParametros, setModalParametros] = useState(false);
    const [reporteParametros, setReporteParametros] = useState(null);

    const [parametros, setParametros] = useState([]);
    const [parametroEditando, setParametroEditando] = useState(null);

    const [parametroValores, setParametroValores] = useState(null);
    const [vistaParametros, setVistaParametros] = useState("parametros");

    const [formParametro, setFormParametro] = useState({
        id_rpar: null,
        id_rpt: "",
        des_rpar: "",
        tip_rpar: "text",
        est_rpar: "a",
    });

    const [valores, setValores] = useState([]);
    const [valorEditando, setValorEditando] = useState(null);

    const [formValor, setFormValor] = useState({
        id_rpv: null,
        id_rpar: "",
        tit_rpv: "",
        val_rpv: "",
        var_rpv: "",
        sql_rpv: "",
    });

    useEffect(() => {
        cargarReportes();
    }, []);

    const cargarReportes = async () => {
        setCargando(true);

        try {
            const res = await getReportesNova();
            if (!res.error) setReportes(res.data || []);
        } catch (error) {
            console.error("Error cargando reportes:", error);
        } finally {
            setCargando(false);
        }
    };

    const abrirModalNuevoReporte = () => {

        setFormReporte({
            des_rpt: "",
            sql_rpt: "",
            est_rpt: "a",
            destino: "i",
            id_men: "",
            id_ano: "",
            id_pla: "",
            difusion: "",
        });

        setModalNuevoReporte(true);
    };

    const cerrarModalNuevoReporte = () => {
        setModalNuevoReporte(false);
    };

    const cambiarCampoNuevoReporte = (campo, valor) => {

        setFormReporte((prev) => ({
            ...prev,
            [campo]: valor,
        }));
    };

    const guardarNuevoReporte = async () => {

        if (!formReporte.des_rpt.trim()) {
            alert("Ingrese la descripción");
            return;
        }

        const res = await guardarReporteNova(formReporte);

        if (!res.error) {

            await cargarReportes();

            cerrarModalNuevoReporte();

        } else {

            alert(res.message || "No se pudo guardar");
        }
    };

    const cambiarDestino = async (id, destino) => {
        const res = await actualizarDestinoReporte(id, destino);

        if (!res.error) {
            setReportes((prev) =>
                prev.map((r) => (r.id_rpt === id ? { ...r, destino } : r))
            );
        }
    };

    const cambiarEstado = async (id, estadoActual) => {
        const nuevoEstado = estadoActual === "a" ? "i" : "a";
        const res = await actualizarEstadoReporte(id, nuevoEstado);

        if (!res.error) {
            setReportes((prev) =>
                prev.map((r) =>
                    r.id_rpt === id ? { ...r, est_rpt: nuevoEstado } : r
                )
            );
        }
    };

    const reportesFiltrados = reportes.filter((reporte) =>
        reporte.des_rpt?.toLowerCase().includes(busqueda.toLowerCase())
    );

    const abrirModalEditar = (reporte) => {
        setReporteEditando({ ...reporte });
        setModalEditar(true);
    };

    const cerrarModalEditar = () => {
        setModalEditar(false);
        setReporteEditando(null);
    };

    const cambiarCampoReporte = (campo, valor) => {
        setReporteEditando((prev) => ({
            ...prev,
            [campo]: valor,
        }));
    };

    const guardarReporte = async () => {
        const res = await actualizarReporteNova(reporteEditando);

        if (!res.error) {
            setReportes((prev) =>
                prev.map((r) =>
                    r.id_rpt === reporteEditando.id_rpt ? reporteEditando : r
                )
            );
            cerrarModalEditar();
        } else {
            alert(res.message || "No se pudo actualizar el reporte");
        }
    };

    const abrirModalParametros = async (reporte) => {
        setReporteParametros(reporte);
        setModalParametros(true);

        const res = await getParametrosReporteNova(reporte.id_rpt);
        if (!res.error) setParametros(res.data || []);

        setFormParametro({
            id_rpar: null,
            id_rpt: reporte.id_rpt,
            des_rpar: "",
            tip_rpar: "text",
            est_rpar: "a",
        });
    };

    const cerrarModalParametros = () => {
        setModalParametros(false);
        setReporteParametros(null);
        setParametros([]);
        setParametroValores(null);
        setValores([]);
        setVistaParametros("parametros");
        limpiarParametro();
        limpiarValor();
    };

    const limpiarParametro = () => {
        setParametroEditando(null);
        setFormParametro({
            id_rpar: null,
            id_rpt: reporteParametros?.id_rpt || "",
            des_rpar: "",
            tip_rpar: "text",
            est_rpar: "a",
        });
    };

    const guardarParametro = async () => {
        if (!formParametro.des_rpar.trim()) {
            alert("Ingrese la descripción del parámetro");
            return;
        }

        const data = {
            ...formParametro,
            id_rpt: reporteParametros.id_rpt,
        };

        const res = parametroEditando
            ? await actualizarParametroReporteNova(data)
            : await guardarParametroReporteNova(data);

        if (!res.error) {
            const nuevos = await getParametrosReporteNova(reporteParametros.id_rpt);
            setParametros(nuevos.data || []);
            limpiarParametro();
        } else {
            alert(res.message || "No se pudo guardar el parámetro");
        }
    };

    const editarParametro = (parametro) => {
        setParametroEditando(parametro);
        setFormParametro({
            id_rpar: parametro.id_rpar,
            id_rpt: parametro.id_rpt,
            des_rpar: parametro.des_rpar || "",
            tip_rpar: parametro.tip_rpar || "text",
            est_rpar: parametro.est_rpar || "a",
        });
    };

    const eliminarParametro = async (id_rpar) => {
        if (!window.confirm("¿Desea eliminar este parámetro?")) return;

        const res = await eliminarParametroReporteNova(id_rpar);

        if (!res.error) {
            setParametros((prev) => prev.filter((p) => p.id_rpar !== id_rpar));
        } else {
            alert(res.message || "No se pudo eliminar el parámetro");
        }
    };

    const abrirModalValores = async (parametro) => {

        setParametroValores(parametro);

        setVistaParametros("valores");

        const res = await getValoresParametroNova(parametro.id_rpar);

        if (!res.error) {
            setValores(res.data || []);
        }

        setFormValor({
            id_rpv: null,
            id_rpar: parametro.id_rpar,
            tit_rpv: "",
            val_rpv: "",
            var_rpv: "",
            sql_rpv: "",
        });
    };



    const limpiarValor = () => {
        setValorEditando(null);
        setFormValor({
            id_rpv: null,
            id_rpar: parametroValores?.id_rpar || "",
            tit_rpv: "",
            val_rpv: "",
            var_rpv: "",
            sql_rpv: "",
        });
    };

    const guardarValor = async () => {
        const data = {
            ...formValor,
            id_rpar: parametroValores.id_rpar,
        };

        const res = valorEditando
            ? await actualizarValorParametroNova(data)
            : await guardarValorParametroNova(data);

        if (!res.error) {
            const nuevos = await getValoresParametroNova(parametroValores.id_rpar);
            setValores(nuevos.data || []);
            limpiarValor();
        } else {
            alert(res.message || "No se pudo guardar el valor");
        }
    };

    const editarValor = (valor) => {
        setValorEditando(valor);
        setFormValor({
            id_rpv: valor.id_rpv,
            id_rpar: valor.id_rpar,
            tit_rpv: valor.tit_rpv || "",
            val_rpv: valor.val_rpv || "",
            var_rpv: valor.var_rpv || "",
            sql_rpv: valor.sql_rpv || "",
        });
    };

    const eliminarValor = async (id_rpv) => {
        if (!window.confirm("¿Desea eliminar este valor?")) return;

        const res = await eliminarValorParametroNova(id_rpv);

        if (!res.error) {
            setValores((prev) => prev.filter((v) => v.id_rpv !== id_rpv));
        } else {
            alert(res.message || "No se pudo eliminar el valor");
        }
    };

    return (
        <>
            <Helmet>
                <script src="https://cdn.tailwindcss.com"></script>
            </Helmet>

            <div className="min-h-screen w-full bg-slate-100 px-3 py-4 md:px-5">
                <div className="mx-auto w-full max-w-7xl">
                    <div className="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-md">
                        <div className="border-b border-sky-100 bg-gradient-to-r from-sky-50 via-white to-emerald-50 px-5 py-4">
                            <div className="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                                <div className="flex items-center gap-3">
                                    <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-sky-100 text-sky-700 ring-1 ring-sky-200">
                                        <FaChartLine />
                                    </div>

                                    <div>
                                        <h1 className="text-lg font-bold text-slate-800 md:text-xl">
                                            Configuración de Parámetros
                                        </h1>
                                        <p className="text-xs text-slate-500">
                                            Administración de reportes, parámetros y valores de Nova SISA.
                                        </p>
                                    </div>
                                </div>

                                <div className="flex items-center gap-2">
                                    <div className="rounded-full bg-sky-100 px-3 py-1.5 text-xs font-semibold text-sky-700 ring-1 ring-sky-200">
                                        Total: {reportesFiltrados.length}
                                    </div>

                                    <button
                                        type="button"
                                        onClick={abrirModalNuevoReporte}
                                        className="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-emerald-700"
                                    >
                                        <FaPlus />
                                        Nuevo reporte
                                    </button>

                                    <button
                                        type="button"
                                        onClick={cargarReportes}
                                        className="inline-flex items-center gap-2 rounded-lg bg-sky-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-sky-700"
                                    >
                                        <FaSyncAlt className={cargando ? "animate-spin" : ""} />
                                        Actualizar
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div className="p-5">
                            <div className="mb-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                                <div>
                                    <h2 className="text-sm font-semibold text-slate-800">
                                        Listado de reportes
                                    </h2>
                                    <p className="text-xs text-slate-500">
                                        Consulta y configura los parámetros disponibles.
                                    </p>
                                </div>

                                <div className="flex h-10 w-full items-center overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm md:w-[380px]">
                                    <div className="flex h-full w-12 items-center justify-center border-r border-slate-200 bg-slate-50">
                                        <FaSearch className="text-xs text-slate-500" />
                                    </div>

                                    <input
                                        type="text"
                                        placeholder="Buscar reporte por nombre..."
                                        value={busqueda}
                                        onChange={(e) => setBusqueda(e.target.value)}
                                        className="h-full flex-1 bg-transparent px-3 text-xs font-medium text-slate-700 outline-none"
                                    />
                                </div>
                            </div>

                            <div className="overflow-hidden rounded-lg border border-slate-200 bg-white">
                                <div className="overflow-x-auto">
                                    <table className="min-w-full text-xs">
                                        <thead className="bg-slate-50">
                                            <tr className="border-b border-slate-200">
                                                <th className="px-4 py-3 text-left font-bold uppercase text-slate-600">
                                                    Reporte
                                                </th>
                                                <th className="px-4 py-3 text-left font-bold uppercase text-slate-600">
                                                    Estado
                                                </th>
                                                <th className="px-4 py-3 text-left font-bold uppercase text-slate-600">
                                                    Acciones
                                                </th>
                                                <th className="px-4 py-3 text-left font-bold uppercase text-slate-600">
                                                    Plantilla
                                                </th>
                                                <th className="px-4 py-3 text-left font-bold uppercase text-slate-600">
                                                    Destino
                                                </th>
                                            </tr>
                                        </thead>

                                        <tbody className="divide-y divide-slate-100">
                                            {cargando && (
                                                <tr>
                                                    <td colSpan="5" className="px-4 py-8 text-center text-slate-500">
                                                        <FaSyncAlt className="mx-auto mb-2 animate-spin" />
                                                        Cargando reportes...
                                                    </td>
                                                </tr>
                                            )}

                                            {!cargando &&
                                                reportesFiltrados.map((reporte) => (
                                                    <tr key={reporte.id_rpt} className="hover:bg-slate-50">
                                                        <td className="px-4 py-3">
                                                            <div className="flex items-center gap-2">
                                                                <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 text-slate-600">
                                                                    <FaFileAlt />
                                                                </div>

                                                                <div>
                                                                    <p className="font-semibold text-slate-800">
                                                                        {reporte.des_rpt}
                                                                    </p>
                                                                    <p className="text-[11px] text-slate-400">
                                                                        ID reporte: {reporte.id_rpt}
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </td>

                                                        <td className="px-4 py-3">
                                                            <button
                                                                type="button"
                                                                onClick={() =>
                                                                    cambiarEstado(reporte.id_rpt, reporte.est_rpt)
                                                                }
                                                                className={`rounded-full px-2.5 py-1 text-[11px] font-bold ${reporte.est_rpt === "a"
                                                                    ? "bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200"
                                                                    : "bg-rose-50 text-rose-700 ring-1 ring-rose-200"
                                                                    }`}
                                                            >
                                                                {reporte.est_rpt === "a" ? "Activa" : "Inactiva"}
                                                            </button>
                                                        </td>

                                                        <td className="px-4 py-3">
                                                            <div className="flex gap-2">
                                                                <button
                                                                    type="button"
                                                                    title="Editar reporte"
                                                                    onClick={() => abrirModalEditar(reporte)}
                                                                    className="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-100"
                                                                >
                                                                    <FaEdit />
                                                                </button>

                                                                <button
                                                                    type="button"
                                                                    title="Configurar parámetros"
                                                                    onClick={() => abrirModalParametros(reporte)}
                                                                    className="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-100"
                                                                >
                                                                    <FaSlidersH />
                                                                </button>
                                                            </div>
                                                        </td>

                                                        <td className="px-4 py-3">
                                                            <select
                                                                value={reporte.id_pla || ""}
                                                                disabled
                                                                className="w-44 rounded-lg border border-slate-300 bg-slate-50 px-2 py-1.5 text-xs text-slate-500"
                                                            >
                                                                <option value="">[Relacionar]</option>
                                                            </select>
                                                        </td>

                                                        <td className="px-4 py-3">
                                                            <select
                                                                value={reporte.destino || "i"}
                                                                onChange={(e) =>
                                                                    cambiarDestino(reporte.id_rpt, e.target.value)
                                                                }
                                                                className="w-44 rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-xs text-slate-700 outline-none focus:border-sky-500"
                                                            >
                                                                <option value="i">INTRANET</option>
                                                                <option value="sp">NOVA SISA PAGE</option>
                                                                <option value="w">MÓDULO WEB</option>
                                                                <option value="p">PÚBLICO</option>
                                                            </select>
                                                        </td>
                                                    </tr>
                                                ))}

                                            {!cargando && reportesFiltrados.length === 0 && (
                                                <tr>
                                                    <td colSpan="5" className="px-4 py-8 text-center text-slate-500">
                                                        No se encontraron reportes.
                                                    </td>
                                                </tr>
                                            )}
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div className="mt-3 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-[11px] text-slate-500">
                                Los cambios se guardan directamente en la base de datos de Nova SISA.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {modalEditar && reporteEditando && (
                <div className="fixed inset-0 z-[200] flex items-start justify-center bg-slate-900/35 pl-[260px] pt-8 backdrop-blur-sm">
                    <div className="ml-10 w-full max-w-3xl overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
                        <div className="flex items-center justify-between border-b border-slate-200 bg-gradient-to-r from-sky-50 via-white to-emerald-50 px-5 py-4">
                            <div className="flex items-center gap-3">
                                <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-sky-100 text-sky-700 ring-1 ring-sky-200">
                                    <FaEdit />
                                </div>

                                <div>
                                    <h3 className="text-base font-bold text-slate-800">
                                        Modificar reporte
                                    </h3>
                                    <p className="text-xs text-slate-500">
                                        Actualiza la información, destino y consulta SQL.
                                    </p>
                                </div>
                            </div>

                            <button
                                onClick={cerrarModalEditar}
                                className="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 hover:bg-slate-100"
                            >
                                ✕
                            </button>
                        </div>

                        <div className="max-h-[68vh] overflow-y-auto bg-slate-50/60 px-5 py-4">
                            <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <Campo label="Nombre del reporte">
                                    <input
                                        value={reporteEditando.des_rpt || ""}
                                        onChange={(e) =>
                                            cambiarCampoReporte("des_rpt", e.target.value)
                                        }
                                        className="input-nova h-10"
                                        placeholder="Nombre del reporte"
                                    />
                                </Campo>

                                <Campo label="Estado">
                                    <select
                                        value={reporteEditando.est_rpt || "a"}
                                        onChange={(e) =>
                                            cambiarCampoReporte("est_rpt", e.target.value)
                                        }
                                        className="input-nova h-10"
                                    >
                                        <option value="a">Activo</option>
                                        <option value="i">Inactivo</option>
                                    </select>
                                </Campo>

                                <Campo label="Destino">
                                    <select
                                        value={reporteEditando.destino || "i"}
                                        onChange={(e) =>
                                            cambiarCampoReporte("destino", e.target.value)
                                        }
                                        className="input-nova h-10"
                                    >
                                        <option value="i">INTRANET</option>
                                        <option value="sp">NOVA SISA PAGE</option>
                                        <option value="w">MÓDULO WEB</option>
                                        <option value="p">PÚBLICO</option>
                                    </select>
                                </Campo>

                                <Campo label="ID Menú">
                                    <input
                                        type="number"
                                        value={reporteEditando.id_men || ""}
                                        onChange={(e) =>
                                            cambiarCampoReporte("id_men", e.target.value)
                                        }
                                        className="input-nova h-10"
                                        placeholder="Opcional"
                                    />
                                </Campo>

                                <Campo label="ID Año">
                                    <input
                                        type="number"
                                        value={reporteEditando.id_ano || ""}
                                        onChange={(e) =>
                                            cambiarCampoReporte("id_ano", e.target.value)
                                        }
                                        className="input-nova h-10"
                                        placeholder="Opcional"
                                    />
                                </Campo>

                                <Campo label="ID Plantilla">
                                    <input
                                        type="number"
                                        value={reporteEditando.id_pla || ""}
                                        onChange={(e) =>
                                            cambiarCampoReporte("id_pla", e.target.value)
                                        }
                                        className="input-nova h-10"
                                        placeholder="Opcional"
                                    />
                                </Campo>

                                <Campo label="Consulta SQL" full>
                                    <div className="overflow-hidden rounded-xl border border-slate-300 bg-slate-950">
                                        <div className="flex items-center justify-between border-b border-slate-700 bg-slate-900 px-3 py-2">
                                            <span className="text-[11px] font-bold uppercase tracking-wide text-slate-300">
                                                Editor SQL
                                            </span>
                                            <span className="rounded-full bg-sky-500/10 px-2 py-0.5 text-[10px] font-semibold text-sky-300 ring-1 ring-sky-500/30">
                                                UPDATE
                                            </span>
                                        </div>

                                        <textarea
                                            rows={5}
                                            value={reporteEditando.sql_rpt || ""}
                                            onChange={(e) =>
                                                cambiarCampoReporte("sql_rpt", e.target.value)
                                            }
                                            className="min-h-[120px] w-full resize-y bg-slate-950 px-3 py-2 font-mono text-xs text-slate-100 outline-none placeholder:text-slate-500"
                                            placeholder="SELECT campo_1, campo_2 FROM tabla WHERE estado = 'a';"
                                        />
                                    </div>
                                </Campo>
                            </div>
                        </div>

                        <div className="flex justify-end gap-3 border-t border-slate-200 bg-white px-5 py-3">
                            <button
                                onClick={cerrarModalEditar}
                                className="btn-secondary"
                            >
                                Cancelar
                            </button>

                            <button
                                onClick={guardarReporte}
                                className="inline-flex items-center justify-center gap-2 rounded-lg bg-sky-600 px-4 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-sky-700"
                            >
                                <FaSave />
                                Guardar cambios
                            </button>
                        </div>
                    </div>
                </div>
            )}

            {modalParametros && reporteParametros && (
                <div className="fixed inset-0 z-[210] flex items-start justify-center bg-slate-900/35 pl-[260px] pt-8 backdrop-blur-sm">
                    <div className="ml-10 w-full max-w-5xl overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
                        <div className="flex items-center justify-between border-b border-slate-200 bg-gradient-to-r from-amber-50 via-white to-sky-50 px-5 py-4">
                            <div className="flex items-center gap-3">
                                <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-100 text-amber-700 ring-1 ring-amber-200">
                                    <FaSlidersH />
                                </div>

                                <div>
                                    <h3 className="text-base font-bold text-slate-800">
                                        Configurar parámetros
                                    </h3>
                                    <p className="text-xs text-slate-500">
                                        Reporte: {reporteParametros.des_rpt}
                                    </p>
                                </div>
                            </div>

                            <button
                                onClick={cerrarModalParametros}
                                className="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 hover:bg-slate-100"
                            >
                                ✕
                            </button>
                        </div>

                        <div className="grid grid-cols-1 gap-4 bg-slate-50/60 p-5 md:grid-cols-[300px_1fr]">
                            <div className="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                                <div className="mb-4 flex items-center justify-between border-b border-slate-100 pb-3">
                                    <div>
                                        <h4 className="text-sm font-bold text-slate-800">
                                            {parametroEditando ? "Modificar parámetro" : "Nuevo parámetro"}
                                        </h4>
                                        <p className="text-xs text-slate-500">
                                            Define el filtro del reporte.
                                        </p>
                                    </div>
                                </div>

                                <CampoSimple label="Descripción">
                                    <input
                                        value={formParametro.des_rpar}
                                        onChange={(e) =>
                                            setFormParametro((p) => ({
                                                ...p,
                                                des_rpar: e.target.value,
                                            }))
                                        }
                                        className="input-nova h-10"
                                        placeholder="Ej: Fecha inicial"
                                    />
                                </CampoSimple>

                                <CampoSimple label="Tipo de dato">
                                    <select
                                        value={formParametro.tip_rpar}
                                        onChange={(e) =>
                                            setFormParametro((p) => ({
                                                ...p,
                                                tip_rpar: e.target.value,
                                            }))
                                        }
                                        className="input-nova h-10"
                                    >
                                        <option value="text">Texto</option>
                                        <option value="number">Número</option>
                                        <option value="date">Fecha</option>
                                        <option value="select">Selección</option>
                                    </select>
                                </CampoSimple>

                                <CampoSimple label="Estado">
                                    <select
                                        value={formParametro.est_rpar}
                                        onChange={(e) =>
                                            setFormParametro((p) => ({
                                                ...p,
                                                est_rpar: e.target.value,
                                            }))
                                        }
                                        className="input-nova h-10"
                                    >
                                        <option value="a">Activo</option>
                                        <option value="i">Inactivo</option>
                                    </select>
                                </CampoSimple>

                                <div className="mt-4 flex gap-2">
                                    <button
                                        onClick={guardarParametro}
                                        className="inline-flex flex-1 items-center justify-center gap-2 rounded-lg bg-amber-500 px-4 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-amber-600"
                                    >
                                        <FaSave />
                                        Guardar
                                    </button>

                                    <button
                                        onClick={limpiarParametro}
                                        className="btn-secondary"
                                    >
                                        Limpiar
                                    </button>
                                </div>
                            </div>

                            <div className="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                                <div className="flex items-center justify-between border-b border-slate-200 bg-white px-4 py-3">
                                    <div>
                                        <h4 className="text-sm font-bold text-slate-800">

                                            {vistaParametros === "parametros"
                                                ? "Parámetros registrados"
                                                : `Valores de ${parametroValores?.des_rpar}`}

                                        </h4>
                                        <p className="text-xs text-slate-500">
                                            Total configurados: {parametros.length}
                                        </p>
                                        {vistaParametros === "valores" && (
                                            <button
                                                onClick={() => {
                                                    setVistaParametros("parametros");
                                                    setParametroValores(null);
                                                    setValores([]);
                                                    limpiarValor();
                                                }}
                                                className="mt-2 rounded-lg border border-slate-200 bg-white px-3 py-1 text-[11px] font-bold text-slate-600 hover:bg-slate-100"
                                            >
                                                ← Volver a parámetros
                                            </button>
                                        )}
                                    </div>

                                    <span className="rounded-full bg-amber-50 px-3 py-1 text-[11px] font-bold text-amber-700 ring-1 ring-amber-200">
                                        Filtros
                                    </span>
                                </div>
                                {vistaParametros === "parametros" && (
                                    <div className="max-h-[430px] overflow-y-auto">
                                        <table className="min-w-full text-xs">
                                            <thead className="sticky top-0 z-10 bg-slate-50">
                                                <tr className="border-b border-slate-200">
                                                    <th className="px-4 py-3 text-left font-bold uppercase text-slate-600">
                                                        Parámetro
                                                    </th>
                                                    <th className="px-4 py-3 text-left font-bold uppercase text-slate-600">
                                                        Tipo
                                                    </th>
                                                    <th className="px-4 py-3 text-left font-bold uppercase text-slate-600">
                                                        Estado
                                                    </th>
                                                    <th className="px-4 py-3 text-center font-bold uppercase text-slate-600">
                                                        Acciones
                                                    </th>
                                                </tr>
                                            </thead>

                                            <tbody className="divide-y divide-slate-100">
                                                {parametros.map((p) => (
                                                    <tr key={p.id_rpar} className="hover:bg-slate-50">
                                                        <td className="px-4 py-3">
                                                            <div className="flex items-center gap-2">
                                                                <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-50 text-amber-700">
                                                                    <FaSlidersH />
                                                                </div>

                                                                <div>
                                                                    <p className="font-semibold text-slate-700">
                                                                        {p.des_rpar}
                                                                    </p>
                                                                    <p className="text-[11px] text-slate-400">
                                                                        ID: {p.id_rpar}
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </td>

                                                        <td className="px-4 py-3 text-slate-500">
                                                            {p.tip_rpar}
                                                        </td>

                                                        <td className="px-4 py-3">
                                                            <span
                                                                className={`rounded-full px-2 py-1 text-[11px] font-bold ${p.est_rpar === "a"
                                                                    ? "bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200"
                                                                    : "bg-rose-50 text-rose-700 ring-1 ring-rose-200"
                                                                    }`}
                                                            >
                                                                {p.est_rpar === "a" ? "Activo" : "Inactivo"}
                                                            </span>
                                                        </td>

                                                        <td className="px-4 py-3">
                                                            <div className="flex justify-center gap-2">
                                                                <button
                                                                    onClick={() => abrirModalValores(p)}
                                                                    className="btn-icon amber"
                                                                    title="Valores"
                                                                >
                                                                    <FaList />
                                                                </button>

                                                                <button
                                                                    onClick={() => editarParametro(p)}
                                                                    className="btn-icon slate"
                                                                    title="Editar"
                                                                >
                                                                    <FaEdit />
                                                                </button>

                                                                <button
                                                                    onClick={() => eliminarParametro(p.id_rpar)}
                                                                    className="btn-icon rose"
                                                                    title="Eliminar"
                                                                >
                                                                    <FaTrash />
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                ))}

                                                {parametros.length === 0 && (
                                                    <tr>
                                                        <td colSpan="4" className="px-4 py-10 text-center text-slate-500">
                                                            Este reporte aún no tiene parámetros configurados.
                                                        </td>
                                                    </tr>
                                                )}
                                            </tbody>
                                        </table>
                                    </div>
                                )}
                                {vistaParametros === "valores" && (
                                    <div className="grid grid-cols-1 gap-3 p-3 md:grid-cols-[240px_1fr]">
                                        <div className="rounded-xl border border-slate-200 bg-white p-3">
                                            <h4 className="mb-3 text-sm font-bold text-slate-800">
                                                {valorEditando ? "Modificar valor" : "Nuevo valor"}
                                            </h4>

                                            <CampoSimple label="Título">
                                                <input
                                                    value={formValor.tit_rpv}
                                                    onChange={(e) =>
                                                        setFormValor((p) => ({ ...p, tit_rpv: e.target.value }))
                                                    }
                                                    className="input-nova h-9"
                                                    placeholder="Título"
                                                />
                                            </CampoSimple>

                                            <CampoSimple label="Valor">
                                                <input
                                                    value={formValor.val_rpv}
                                                    onChange={(e) =>
                                                        setFormValor((p) => ({ ...p, val_rpv: e.target.value }))
                                                    }
                                                    className="input-nova h-9"
                                                    placeholder="Valor"
                                                />
                                            </CampoSimple>

                                            <CampoSimple label="Variable">
                                                <input
                                                    value={formValor.var_rpv}
                                                    onChange={(e) =>
                                                        setFormValor((p) => ({ ...p, var_rpv: e.target.value }))
                                                    }
                                                    className="input-nova h-9"
                                                    placeholder="Variable"
                                                />
                                            </CampoSimple>

                                            <CampoSimple label="SQL">
                                                <textarea
                                                    rows={2}
                                                    value={formValor.sql_rpv}
                                                    onChange={(e) =>
                                                        setFormValor((p) => ({ ...p, sql_rpv: e.target.value }))
                                                    }
                                                    className="input-nova min-h-[64px] font-mono text-xs"
                                                    placeholder="SELECT ..."
                                                />
                                            </CampoSimple>

                                            <div className="mt-3 flex gap-2">
                                                <button
                                                    onClick={guardarValor}
                                                    className="inline-flex flex-1 items-center justify-center gap-2 rounded-lg bg-sky-600 px-3 py-2 text-xs font-bold text-white hover:bg-sky-700"
                                                >
                                                    <FaSave />
                                                    Guardar
                                                </button>

                                                <button onClick={limpiarValor} className="btn-secondary px-3">
                                                    Limpiar
                                                </button>
                                            </div>
                                        </div>

                                        <div className="overflow-hidden rounded-xl border border-slate-200 bg-white">
                                            <div className="flex items-center justify-between border-b border-slate-200 bg-slate-50 px-3 py-2">
                                                <div>
                                                    <h4 className="text-sm font-bold text-slate-800">
                                                        Valores registrados
                                                    </h4>
                                                    <p className="text-[11px] text-slate-500">
                                                        Total: {valores.length}
                                                    </p>
                                                </div>

                                                <span className="rounded-full bg-sky-50 px-2.5 py-1 text-[10px] font-bold text-sky-700 ring-1 ring-sky-200">
                                                    Opciones
                                                </span>
                                            </div>

                                            <div className="max-h-[300px] overflow-y-auto">
                                                <table className="min-w-full text-xs">
                                                    <thead className="sticky top-0 z-10 bg-white">
                                                        <tr className="border-b border-slate-200">
                                                            <th className="px-3 py-2 text-left font-bold uppercase text-slate-600">
                                                                Título
                                                            </th>
                                                            <th className="px-3 py-2 text-left font-bold uppercase text-slate-600">
                                                                Valor
                                                            </th>
                                                            <th className="px-3 py-2 text-left font-bold uppercase text-slate-600">
                                                                Variable
                                                            </th>
                                                            <th className="px-3 py-2 text-center font-bold uppercase text-slate-600">
                                                                Acciones
                                                            </th>
                                                        </tr>
                                                    </thead>

                                                    <tbody className="divide-y divide-slate-100">
                                                        {valores.map((v) => (
                                                            <tr key={v.id_rpv} className="hover:bg-slate-50">
                                                                <td className="px-3 py-2">
                                                                    <p className="font-semibold text-slate-700">
                                                                        {v.tit_rpv || "Sin título"}
                                                                    </p>
                                                                    <p className="text-[10px] text-slate-400">
                                                                        ID: {v.id_rpv}
                                                                    </p>
                                                                </td>

                                                                <td className="px-3 py-2 text-slate-500">
                                                                    {v.val_rpv || "-"}
                                                                </td>

                                                                <td className="px-3 py-2">
                                                                    <span className="rounded-md bg-slate-100 px-2 py-1 font-mono text-[10px] text-slate-600">
                                                                        {v.var_rpv || "-"}
                                                                    </span>
                                                                </td>

                                                                <td className="px-3 py-2">
                                                                    <div className="flex justify-center gap-2">
                                                                        <button
                                                                            onClick={() => editarValor(v)}
                                                                            className="btn-icon slate"
                                                                            title="Editar"
                                                                        >
                                                                            <FaEdit />
                                                                        </button>

                                                                        <button
                                                                            onClick={() => eliminarValor(v.id_rpv)}
                                                                            className="btn-icon rose"
                                                                            title="Eliminar"
                                                                        >
                                                                            <FaTrash />
                                                                        </button>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        ))}

                                                        {valores.length === 0 && (
                                                            <tr>
                                                                <td colSpan="4" className="px-3 py-10 text-center text-slate-500">
                                                                    Este parámetro aún no tiene valores configurados.
                                                                </td>
                                                            </tr>
                                                        )}
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            )}

            {modalNuevoReporte && (
                <div className="fixed inset-0 z-[230] flex items-start justify-center bg-slate-900/35 pl-[260px] pt-8 backdrop-blur-sm">
                    <div className="ml-10 w-full max-w-3xl overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
                        <div className="flex items-center justify-between border-b border-slate-200 bg-gradient-to-r from-sky-50 via-white to-emerald-50 px-5 py-4">
                            <div className="flex items-center gap-3">
                                <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700 ring-1 ring-emerald-200">
                                    <FaPlus />
                                </div>

                                <div>
                                    <h3 className="text-base font-bold text-slate-800">
                                        Crear reporte Nova SISA
                                    </h3>
                                    <p className="text-xs text-slate-500">
                                        Información base y consulta SQL del reporte.
                                    </p>
                                </div>
                            </div>

                            <button
                                onClick={cerrarModalNuevoReporte}
                                className="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 hover:bg-slate-100"
                            >
                                ✕
                            </button>
                        </div>

                        <div className="max-h-[68vh] overflow-y-auto bg-slate-50/60 px-5 py-4">
                            <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <Campo label="Nombre del reporte">
                                    <input
                                        value={formReporte.des_rpt}
                                        onChange={(e) =>
                                            cambiarCampoNuevoReporte("des_rpt", e.target.value)
                                        }
                                        className="input-nova h-10"
                                        placeholder="Ej: Reporte de vigilancia"
                                    />
                                </Campo>

                                <Campo label="Estado">
                                    <select
                                        value={formReporte.est_rpt}
                                        onChange={(e) =>
                                            cambiarCampoNuevoReporte("est_rpt", e.target.value)
                                        }
                                        className="input-nova h-10"
                                    >
                                        <option value="a">Activo</option>
                                        <option value="i">Inactivo</option>
                                    </select>
                                </Campo>

                                <Campo label="Destino">
                                    <select
                                        value={formReporte.destino}
                                        onChange={(e) =>
                                            cambiarCampoNuevoReporte("destino", e.target.value)
                                        }
                                        className="input-nova h-10"
                                    >
                                        <option value="i">INTRANET</option>
                                        <option value="sp">NOVA SISA PAGE</option>
                                        <option value="w">MÓDULO WEB</option>
                                        <option value="p">PÚBLICO</option>
                                    </select>
                                </Campo>

                                <Campo label="ID Menú">
                                    <input
                                        type="number"
                                        value={formReporte.id_men}
                                        onChange={(e) =>
                                            cambiarCampoNuevoReporte("id_men", e.target.value)
                                        }
                                        className="input-nova h-10"
                                        placeholder="Opcional"
                                    />
                                </Campo>

                                <Campo label="ID Año">
                                    <input
                                        type="number"
                                        value={formReporte.id_ano}
                                        onChange={(e) =>
                                            cambiarCampoNuevoReporte("id_ano", e.target.value)
                                        }
                                        className="input-nova h-10"
                                        placeholder="Opcional"
                                    />
                                </Campo>

                                <Campo label="Difusión">
                                    <input
                                        value={formReporte.difusion}
                                        onChange={(e) =>
                                            cambiarCampoNuevoReporte("difusion", e.target.value)
                                        }
                                        className="input-nova h-10"
                                        placeholder="Opcional"
                                    />
                                </Campo>

                                <Campo label="Consulta SQL" full>
                                    <div className="overflow-hidden rounded-xl border border-slate-300 bg-slate-950">
                                        <div className="flex items-center justify-between border-b border-slate-700 bg-slate-900 px-3 py-2">
                                            <span className="text-[11px] font-bold uppercase tracking-wide text-slate-300">
                                                Editor SQL
                                            </span>
                                            <span className="rounded-full bg-sky-500/10 px-2 py-0.5 text-[10px] font-semibold text-sky-300 ring-1 ring-sky-500/30">
                                                SELECT
                                            </span>
                                        </div>

                                        <textarea
                                            rows={5}
                                            value={formReporte.sql_rpt}
                                            onChange={(e) =>
                                                cambiarCampoNuevoReporte("sql_rpt", e.target.value)
                                            }
                                            className="min-h-[120px] w-full resize-y bg-slate-950 px-3 py-2 font-mono text-xs text-slate-100 outline-none placeholder:text-slate-500"
                                            placeholder="SELECT campo_1, campo_2 FROM tabla WHERE estado = 'a';"
                                        />
                                    </div>
                                </Campo>
                            </div>
                        </div>

                        <div className="flex justify-end gap-3 border-t border-slate-200 bg-white px-5 py-3">
                            <button
                                onClick={cerrarModalNuevoReporte}
                                className="btn-secondary"
                            >
                                Cancelar
                            </button>

                            <button
                                onClick={guardarNuevoReporte}
                                className="inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-emerald-700"
                            >
                                <FaSave />
                                Guardar reporte
                            </button>
                        </div>
                    </div>
                </div>
            )}

            <style>{`
        .input-nova {
          width: 100%;
          border-radius: 0.5rem;
          border: 1px solid rgb(203 213 225);
          padding: 0.5rem 0.75rem;
          font-size: 0.875rem;
          outline: none;
          background: white;
        }

        .input-nova:focus {
          border-color: rgb(14 165 233);
          box-shadow: 0 0 0 2px rgb(224 242 254);
        }

        .btn-primary {
          display: inline-flex;
          align-items: center;
          justify-content: center;
          gap: 0.5rem;
          border-radius: 0.5rem;
          background: rgb(2 132 199);
          padding: 0.5rem 1rem;
          font-size: 0.75rem;
          font-weight: 700;
          color: white;
        }

        .btn-primary:hover {
          background: rgb(3 105 161);
        }

        .btn-secondary {
          border-radius: 0.5rem;
          border: 1px solid rgb(203 213 225);
          background: white;
          padding: 0.5rem 1rem;
          font-size: 0.75rem;
          font-weight: 700;
          color: rgb(71 85 105);
        }

        .btn-secondary:hover {
          background: rgb(241 245 249);
        }

        .btn-icon {
          display: inline-flex;
          height: 2rem;
          width: 2rem;
          align-items: center;
          justify-content: center;
          border-radius: 0.5rem;
          border: 1px solid rgb(226 232 240);
        }

        .btn-icon.slate {
          color: rgb(71 85 105);
        }

        .btn-icon.slate:hover {
          background: rgb(241 245 249);
        }

        .btn-icon.amber {
          background: rgb(255 251 235);
          color: rgb(180 83 9);
          border-color: rgb(253 230 138);
        }

        .btn-icon.amber:hover {
          background: rgb(254 243 199);
        }

        .btn-icon.rose {
          background: rgb(255 241 242);
          color: rgb(190 18 60);
          border-color: rgb(254 205 211);
        }

        .btn-icon.rose:hover {
          background: rgb(255 228 230);
        }
      `}</style>
        </>
    );
}

function Campo({ label, children, full = false }) {
    return (
        <div className={full ? "md:col-span-2" : ""}>
            <label className="mb-1 block text-sm font-semibold text-slate-700">
                {label}
            </label>
            {children}
        </div>
    );
}

function CampoSimple({ label, children }) {
    return (
        <div className="mb-3">
            <label className="mb-1 block text-xs font-semibold text-slate-600">
                {label}
            </label>
            {children}
        </div>
    );
}