import React, { useMemo, useState } from "react";
import * as XLSX from "xlsx";
import {
    FaArrowLeft,
    FaChartBar,
    FaTable,
    FaFileExcel,
    FaSlidersH,
    FaDatabase,
    FaLayerGroup,
    FaCalculator,
    FaChartPie,
} from "react-icons/fa";
import {
    BarChart,
    Bar,
    LineChart,
    Line,
    AreaChart,
    Area,
    PieChart,
    Pie,
    Cell,
    XAxis,
    YAxis,
    CartesianGrid,
    Tooltip,
    Legend,
    ResponsiveContainer,
} from "recharts";

const COLORS = [
    "#0EA5E9",
    "#10B981",
    "#F59E0B",
    "#EF4444",
    "#8B5CF6",
    "#EC4899",
    "#14B8A6",
    "#F97316",
    "#6366F1",
    "#84CC16",
];

export default function DashboardReporteNova({ resultado, onVolver }) {
    const reporte = resultado?.reporte || {};
    const datos = resultado?.data || resultado?.datos || [];
    const columnas = resultado?.columnas || [];

    const columnasDetectadas = columnas.length
        ? columnas
        : datos.length
            ? Object.keys(datos[0])
            : [];

    const columnasNumericas = useMemo(() => {
        return columnasDetectadas.filter((col) =>
            datos.some((fila) => !isNaN(parseFloat(fila[col])))
        );
    }, [columnasDetectadas, datos]);

    const columnasTexto = useMemo(() => {
        return columnasDetectadas.filter(
            (col) => !columnasNumericas.includes(col)
        );
    }, [columnasDetectadas, columnasNumericas]);

    const [tipoGrafica, setTipoGrafica] = useState(reporte.tp_gr || "barra");
    const [ejeX, setEjeX] = useState(
        reporte.eje_x || columnasTexto[0] || columnasDetectadas[0] || ""
    );
    const [ejeY, setEjeY] = useState(
        reporte.eje_y || columnasNumericas[0] || ""
    );
    const [agrupacion, setAgrupacion] = useState("sum");
    const [limite, setLimite] = useState(10);
    const [busqueda, setBusqueda] = useState("");
    const [paginaActual, setPaginaActual] = useState(1);
    const [filasPorPagina, setFilasPorPagina] = useState(10);

    const datosGrafica = useMemo(() => {
        if (!datos.length || !ejeX) return [];

        const grupos = {};

        datos.forEach((fila) => {
            const key = fila[ejeX] || "Sin dato";

            if (!grupos[key]) {
                grupos[key] = {
                    [ejeX]: key,
                    total: 0,
                    cantidad: 0,
                    valores: [],
                };
            }

            const valorNumerico = parseFloat(fila[ejeY]);
            grupos[key].cantidad += 1;

            if (!isNaN(valorNumerico)) {
                grupos[key].total += valorNumerico;
                grupos[key].valores.push(valorNumerico);
            }
        });

        let data = Object.values(grupos).map((item) => {
            let valor = item.total;

            if (agrupacion === "count") valor = item.cantidad;
            if (agrupacion === "avg") {
                valor = item.valores.length ? item.total / item.valores.length : 0;
            }
            if (agrupacion === "max") {
                valor = item.valores.length ? Math.max(...item.valores) : 0;
            }
            if (agrupacion === "min") {
                valor = item.valores.length ? Math.min(...item.valores) : 0;
            }

            return {
                [ejeX]: item[ejeX],
                valor: Number(valor.toFixed(2)),
                registros: item.cantidad,
            };
        });

        data = data.sort((a, b) => b.valor - a.valor);

        if (limite !== "todos") {
            data = data.slice(0, Number(limite));
        }

        return data;
    }, [datos, ejeX, ejeY, agrupacion, limite]);

    const datosTabla = useMemo(() => {
        if (!busqueda.trim()) return datos;

        const texto = busqueda.toLowerCase();

        return datos.filter((fila) =>
            columnasDetectadas.some((col) =>
                String(fila[col] ?? "").toLowerCase().includes(texto)
            )
        );
    }, [datos, busqueda, columnasDetectadas]);

    const totalPaginas = Math.ceil(datosTabla.length / filasPorPagina) || 1;

    const datosTablaPaginados = useMemo(() => {
        const inicio = (paginaActual - 1) * filasPorPagina;
        const fin = inicio + filasPorPagina;

        return datosTabla.slice(inicio, fin);
    }, [datosTabla, paginaActual, filasPorPagina]);

    const irPaginaAnterior = () => {
        setPaginaActual((prev) => Math.max(prev - 1, 1));
    };

    const irPaginaSiguiente = () => {
        setPaginaActual((prev) => Math.min(prev + 1, totalPaginas));
    };


    const totalValor = useMemo(() => {
        return datosGrafica.reduce((acc, item) => acc + Number(item.valor || 0), 0);
    }, [datosGrafica]);

    const mayorValor = datosGrafica[0];

    const exportarExcel = () => {
        const datosExportar = datosTabla.length ? datosTabla : datos;

        if (!datosExportar.length) {
            alert("No hay datos para exportar.");
            return;
        }

        const datosFormateados = datosExportar.map((fila) => {
            const nuevaFila = {};

            columnasDetectadas.forEach((col) => {
                nuevaFila[col.replaceAll("_", " ").toUpperCase()] = fila[col] ?? "";
            });

            return nuevaFila;
        });

        const hoja = XLSX.utils.json_to_sheet(datosFormateados);

        const libro = XLSX.utils.book_new();

        XLSX.utils.book_append_sheet(libro, hoja, "Reporte");

        const nombreReporte = reporte.des_rpt
            ? reporte.des_rpt.replaceAll(" ", "_").replace(/[^\w]/g, "")
            : "reporte_nova";

        const fecha = new Date().toISOString().slice(0, 10);

        XLSX.writeFile(libro, `${nombreReporte}_${fecha}.xlsx`);
    };

    const renderGraficaPrincipal = () => {
        if (!datosGrafica.length) {
            return (
                <div className="flex h-[320px] items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white text-sm text-slate-500">
                    No hay datos suficientes para graficar.
                </div>
            );
        }

        if (tipoGrafica === "linea") {
            return (
                <ResponsiveContainer width="100%" height={320}>
                    <LineChart data={datosGrafica}>
                        <CartesianGrid strokeDasharray="3 3" stroke="#E2E8F0" />
                        <XAxis dataKey={ejeX} tick={{ fontSize: 11 }} />
                        <YAxis tick={{ fontSize: 11 }} />
                        <Tooltip />
                        <Legend />
                        <Line
                            type="monotone"
                            dataKey="valor"
                            stroke="#0EA5E9"
                            strokeWidth={3}
                            dot={{ r: 4, fill: "#0EA5E9" }}
                            activeDot={{ r: 7 }}
                        />
                    </LineChart>
                </ResponsiveContainer>
            );
        }

        if (tipoGrafica === "area") {
            return (
                <ResponsiveContainer width="100%" height={320}>
                    <AreaChart data={datosGrafica}>
                        <CartesianGrid strokeDasharray="3 3" stroke="#E2E8F0" />
                        <XAxis dataKey={ejeX} tick={{ fontSize: 11 }} />
                        <YAxis tick={{ fontSize: 11 }} />
                        <Tooltip />
                        <Legend />
                        <Area
                            type="monotone"
                            dataKey="valor"
                            stroke="#0EA5E9"
                            fill="#BAE6FD"
                            strokeWidth={3}
                        />
                    </AreaChart>
                </ResponsiveContainer>
            );
        }

        return (
            <ResponsiveContainer width="100%" height={320}>
                <BarChart data={datosGrafica}>
                    <CartesianGrid strokeDasharray="3 3" stroke="#E2E8F0" />
                    <XAxis dataKey={ejeX} tick={{ fontSize: 11 }} />
                    <YAxis tick={{ fontSize: 11 }} />
                    <Tooltip />
                    <Legend />
                    <Bar dataKey="valor" radius={[8, 8, 0, 0]}>
                        {datosGrafica.map((_, index) => (
                            <Cell key={index} fill={COLORS[index % COLORS.length]} />
                        ))}
                    </Bar>
                </BarChart>
            </ResponsiveContainer>
        );
    };

    const renderGraficaCircular = () => {
        if (!datosGrafica.length) {
            return (
                <div className="flex h-[230px] items-center justify-center text-sm text-slate-400">
                    Sin datos
                </div>
            );
        }

        return (
            <ResponsiveContainer width="100%" height={230}>
                <PieChart>
                    <Pie
                        data={datosGrafica}
                        dataKey="valor"
                        nameKey={ejeX}
                        innerRadius={55}
                        outerRadius={85}
                        paddingAngle={3}
                    >
                        {datosGrafica.map((_, index) => (
                            <Cell key={index} fill={COLORS[index % COLORS.length]} />
                        ))}
                    </Pie>
                    <Tooltip />
                </PieChart>
            </ResponsiveContainer>
        );
    };

    return (
        <div className="min-h-screen w-full bg-[#eef3f7] px-4 py-5 md:px-8">
            <div className="mx-auto w-full max-w-7xl space-y-5">
                <div className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                        <div>
                            <h1 className="text-xl font-bold text-slate-800">
                                Dashboard - {reporte.des_rpt}
                            </h1>
                            <p className="text-sm text-slate-500">
                                Analítica dinámica generada desde Nova SISA
                            </p>
                        </div>

                        <div className="flex flex-wrap gap-2">
                            <button
                                onClick={onVolver}
                                className="flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                            >
                                <FaArrowLeft />
                                Volver
                            </button>

                            <button
                                onClick={exportarExcel}
                                className="flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700"
                            >
                                <FaFileExcel />
                                Excel
                            </button>
                        </div>
                    </div>
                </div>

                <div className="grid grid-cols-1 gap-4 lg:grid-cols-12">
                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:col-span-4">
                        <MetricCard
                            icon={<FaDatabase />}
                            title="Registros"
                            value={datos.length}
                            subtitle="Filas generadas"
                            color="sky"
                        />

                        <MetricCard
                            icon={<FaChartBar />}
                            title="Datos graficados"
                            value={datosGrafica.length}
                            subtitle={`Top ${limite}`}
                            color="emerald"
                        />

                        <MetricCard
                            icon={<FaCalculator />}
                            title="Total métrica"
                            value={totalValor.toFixed(2)}
                            subtitle={agrupacion === "count" ? "Conteo total" : ejeY}
                            color="amber"
                        />

                        <MetricCard
                            icon={<FaLayerGroup />}
                            title="Mayor valor"
                            value={mayorValor?.valor ?? 0}
                            subtitle={mayorValor?.[ejeX] || "Sin dato"}
                            color="violet"
                        />
                    </div>

                    <div className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm lg:col-span-4">
                        <div className="mb-2 flex items-center justify-between">
                            <h3 className="flex items-center gap-2 text-sm font-bold text-slate-700">
                                <FaChartPie className="text-sky-600" />
                                Distribución
                            </h3>
                            <span className="rounded-full bg-sky-50 px-3 py-1 text-xs font-bold text-sky-700">
                                {tipoGrafica}
                            </span>
                        </div>

                        {renderGraficaCircular()}

                        <div className="grid grid-cols-2 gap-2 text-xs text-slate-500">
                            <p>
                                <span className="font-bold text-slate-700">Agrupar:</span>{" "}
                                {ejeX}
                            </p>
                            <p>
                                <span className="font-bold text-slate-700">Métrica:</span>{" "}
                                {agrupacion === "count" ? "Conteo" : ejeY}
                            </p>
                        </div>
                    </div>

                    <div className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm lg:col-span-4">
                        <h3 className="mb-4 flex items-center gap-2 text-sm font-bold text-slate-700">
                            <FaSlidersH className="text-sky-600" />
                            Personalizar
                        </h3>

                        <div className="grid grid-cols-1 gap-3">
                            <SelectBox label="Tipo" value={tipoGrafica} onChange={setTipoGrafica}>
                                <option value="barra">Barras</option>
                                <option value="linea">Línea</option>
                                <option value="area">Área</option>
                            </SelectBox>

                            <SelectBox label="Agrupar por" value={ejeX} onChange={setEjeX}>
                                {columnasDetectadas.map((col) => (
                                    <option key={col} value={col}>
                                        {col}
                                    </option>
                                ))}
                            </SelectBox>

                            <SelectBox
                                label="Valor"
                                value={ejeY}
                                onChange={setEjeY}
                                disabled={agrupacion === "count"}
                            >
                                {columnasNumericas.map((col) => (
                                    <option key={col} value={col}>
                                        {col}
                                    </option>
                                ))}
                            </SelectBox>

                            <div className="grid grid-cols-2 gap-3">
                                <SelectBox label="Cálculo" value={agrupacion} onChange={setAgrupacion}>
                                    <option value="sum">Suma</option>
                                    <option value="count">Conteo</option>
                                    <option value="avg">Promedio</option>
                                    <option value="max">Máximo</option>
                                    <option value="min">Mínimo</option>
                                </SelectBox>

                                <SelectBox label="Mostrar" value={limite} onChange={setLimite}>
                                    <option value="5">Top 5</option>
                                    <option value="10">Top 10</option>
                                    <option value="20">Top 20</option>
                                    <option value="todos">Todos</option>
                                </SelectBox>
                            </div>
                        </div>
                    </div>
                </div>

                <div className="grid grid-cols-1 gap-4 lg:grid-cols-12">
                    <div className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm lg:col-span-8">
                        <div className="mb-4 flex items-center justify-between">
                            <h3 className="flex items-center gap-2 text-sm font-bold text-slate-700">
                                <FaChartBar className="text-sky-600" />
                                Tendencia principal
                            </h3>
                            <span className="text-xs text-slate-400">
                                {ejeX} / {agrupacion === "count" ? "Conteo" : ejeY}
                            </span>
                        </div>

                        {renderGraficaPrincipal()}
                    </div>

                    <div className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm lg:col-span-4">
                        <h3 className="mb-4 text-sm font-bold text-slate-700">
                            Ranking
                        </h3>

                        <div className="space-y-3">
                            {datosGrafica.slice(0, 7).map((item, index) => (
                                <div key={index}>
                                    <div className="mb-1 flex items-center justify-between text-xs">
                                        <span className="max-w-[180px] truncate font-semibold text-slate-600">
                                            {item[ejeX]}
                                        </span>
                                        <span className="font-bold text-slate-800">
                                            {item.valor}
                                        </span>
                                    </div>
                                    <div className="h-2 overflow-hidden rounded-full bg-slate-100">
                                        <div
                                            className="h-full rounded-full"
                                            style={{
                                                width: `${Math.min(
                                                    100,
                                                    (item.valor / (mayorValor?.valor || 1)) * 100
                                                )}%`,
                                                backgroundColor: COLORS[index % COLORS.length],
                                            }}
                                        />
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                </div>

                <div className="rounded-[22px] border border-slate-200 bg-white p-5 shadow-[0_10px_30px_rgba(15,23,42,0.08)]">
                    <div className="mb-5 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                        <div className="flex items-start gap-3">
                            <span className="flex h-11 w-11 items-center justify-center rounded-2xl bg-blue-50 text-lg text-blue-600 ring-1 ring-blue-100">
                                <FaTable />
                            </span>

                            <div>
                                <h3 className="text-lg font-extrabold text-slate-800">
                                    Tabla de resultados
                                </h3>
                                <p className="text-sm text-slate-500">
                                    Consulta los datos generados por el reporte
                                </p>
                            </div>
                        </div>

                        <div className="flex w-full flex-col gap-3 md:w-auto md:flex-row">
                            <input
                                type="text"
                                value={busqueda}
                                onChange={(e) => {
                                    setBusqueda(e.target.value);
                                    setPaginaActual(1);
                                }}
                                placeholder="Buscar en resultados..."
                                className="h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm text-slate-700 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-blue-400 focus:ring-4 focus:ring-blue-100 md:w-96"
                            />
                        </div>
                    </div>

                    <div className="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <div className="max-h-[540px] overflow-auto">
                            <table className="min-w-full border-collapse text-sm">
                                <thead className="sticky top-0 z-20 bg-slate-50 text-slate-700">
                                    <tr>
                                        {columnasDetectadas.map((col) => (
                                            <th
                                                key={col}
                                                className="whitespace-nowrap border-b border-r border-slate-200 px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-700 last:border-r-0"
                                            >
                                                {col.replaceAll("_", " ")}
                                            </th>
                                        ))}
                                    </tr>
                                </thead>

                                <tbody>
                                    {datosTablaPaginados.map((fila, index) => (
                                        <tr
                                            key={index}
                                            className="border-b border-slate-100 transition last:border-b-0 hover:bg-blue-50/50"
                                        >
                                            {columnasDetectadas.map((col) => (
                                                <td
                                                    key={col}
                                                    title={fila[col] ?? ""}
                                                    className="max-w-[420px] border-r border-slate-100 px-5 py-4 text-sm font-medium text-slate-700 last:border-r-0"
                                                >
                                                    <span className="block truncate">
                                                        {fila[col] ?? ""}
                                                    </span>
                                                </td>
                                            ))}
                                        </tr>
                                    ))}

                                    {!datosTabla.length && (
                                        <tr>
                                            <td
                                                colSpan={columnasDetectadas.length || 1}
                                                className="px-5 py-12 text-center text-sm text-slate-500"
                                            >
                                                No hay datos para mostrar.
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div className="mt-5 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                        <div className="rounded-2xl border border-slate-200 bg-white px-5 py-3 shadow-sm">
                            <p className="text-sm text-slate-500">
                                Mostrando{" "}
                                <span className="font-extrabold text-slate-800">
                                    {datosTabla.length === 0
                                        ? 0
                                        : (paginaActual - 1) * filasPorPagina + 1}
                                </span>{" "}
                                a{" "}
                                <span className="font-extrabold text-slate-800">
                                    {Math.min(paginaActual * filasPorPagina, datosTabla.length)}
                                </span>{" "}
                                de{" "}
                                <span className="font-extrabold text-slate-800">
                                    {datosTabla.length}
                                </span>{" "}
                                registros
                            </p>
                        </div>

                        <div className="flex flex-wrap items-center justify-center gap-2">
                            <button
                                onClick={irPaginaAnterior}
                                disabled={paginaActual === 1}
                                className="flex h-11 w-11 items-center justify-center rounded-xl border border-slate-200 bg-white text-sm font-bold text-slate-500 shadow-sm transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40"
                            >
                                ‹
                            </button>

                            {Array.from({ length: totalPaginas }, (_, i) => i + 1)
                                .slice(
                                    Math.max(0, paginaActual - 3),
                                    Math.min(totalPaginas, paginaActual + 2)
                                )
                                .map((pagina) => (
                                    <button
                                        key={pagina}
                                        onClick={() => setPaginaActual(pagina)}
                                        className={`flex h-11 w-11 items-center justify-center rounded-xl text-sm font-extrabold shadow-sm transition ${paginaActual === pagina
                                            ? "bg-blue-600 text-white shadow-blue-200"
                                            : "border border-slate-200 bg-white text-blue-600 hover:bg-blue-50"
                                            }`}
                                    >
                                        {pagina}
                                    </button>
                                ))}

                            <button
                                onClick={irPaginaSiguiente}
                                disabled={paginaActual === totalPaginas}
                                className="flex h-11 w-11 items-center justify-center rounded-xl border border-slate-200 bg-white text-sm font-bold text-slate-500 shadow-sm transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40"
                            >
                                ›
                            </button>
                        </div>

                        <div className="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-5 py-3 shadow-sm">
                            <span className="text-sm font-semibold text-slate-500">
                                Filas por página:
                            </span>

                            <select
                                value={filasPorPagina}
                                onChange={(e) => {
                                    setFilasPorPagina(Number(e.target.value));
                                    setPaginaActual(1);
                                }}
                                className="h-10 rounded-xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-700 outline-none focus:border-blue-400 focus:ring-4 focus:ring-blue-100"
                            >
                                <option value={10}>10</option>
                                <option value={25}>25</option>
                                <option value={50}>50</option>
                                <option value={100}>100</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    );
}

function MetricCard({ icon, title, value, subtitle, color }) {
    const colors = {
        sky: "bg-sky-50 text-sky-700",
        emerald: "bg-emerald-50 text-emerald-700",
        amber: "bg-amber-50 text-amber-700",
        violet: "bg-violet-50 text-violet-700",
    };

    return (
        <div className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div className="flex items-start justify-between">
                <div>
                    <p className="text-xs font-bold uppercase tracking-wide text-slate-400">
                        {title}
                    </p>
                    <h2 className="mt-2 text-2xl font-extrabold text-slate-800">
                        {value}
                    </h2>
                    <p className="mt-1 text-xs text-slate-500">{subtitle}</p>
                </div>

                <div className={`rounded-xl p-3 text-lg ${colors[color]}`}>
                    {icon}
                </div>
            </div>
        </div>
    );
}

function SelectBox({ label, value, onChange, children, disabled = false }) {
    return (
        <div>
            <label className="mb-1 block text-xs font-bold uppercase text-slate-500">
                {label}
            </label>
            <select
                value={value}
                disabled={disabled}
                onChange={(e) => onChange(e.target.value)}
                className="h-10 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm text-slate-700 outline-none focus:border-sky-500 focus:ring-4 focus:ring-sky-100 disabled:bg-slate-100"
            >
                {children}
            </select>
        </div>
    );
}