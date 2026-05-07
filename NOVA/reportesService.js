const apiKey = "c683e33c4117d8c319312672ab5a80d5";
const API_BASE = "/nova-sisa/ApiNovaSisa/v1/index.php";

const headers = {
  "Content-Type": "application/json",
  authorization: apiKey,
};

async function post(endpoint, body = {}) {
  const response = await fetch(`${API_BASE}/${endpoint}`, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
  });

  const text = await response.text();

  try {
    return JSON.parse(text);
  } catch (error) {
    console.error("Endpoint:", endpoint);
    console.error("Respuesta de la API:", text);

    return {
      error: true,
      message: "La API no devolvió JSON válido. Revisa la consola del navegador.",
      response: text,
    };
  }
}

/* ================= REPORTES ================= */

export async function getReportesNova() {
  return await post("getReportesNova");
}

export async function actualizarDestinoReporte(id_rpt, destino) {
  return await post("actualizarDestinoReporte", {
    id_rpt,
    destino,
  });
}

export async function actualizarEstadoReporte(id_rpt, est_rpt) {
  return await post("actualizarEstadoReporte", {
    id_rpt,
    est_rpt,
  });
}

export async function actualizarReporteNova(data) {
  return await post("actualizarReporteNova", data);
}

export async function guardarReporteNova(data) {
  return await post("guardarReporteNova", data);
}
/* ================= PARÁMETROS ================= */

export async function getParametrosReporteNova(id_rpt) {
  return await post("getParametrosReporteNova", {
    id_rpt,
  });
}

export async function guardarParametroReporteNova(data) {
  return await post("guardarParametroReporteNova", data);
}

export async function actualizarParametroReporteNova(data) {
  return await post("actualizarParametroReporteNova", data);
}

export async function eliminarParametroReporteNova(id_rpar) {
  return await post("eliminarParametroReporteNova", {
    id_rpar,
  });
}

/* ================= VALORES DE PARÁMETROS ================= */

export async function getValoresParametroNova(id_rpar) {
  return await post("getValoresParametroNova", {
    id_rpar,
  });
}

export async function guardarValorParametroNova(data) {
  return await post("guardarValorParametroNova", data);
}

export async function actualizarValorParametroNova(data) {
  return await post("actualizarValorParametroNova", data);
}

export async function eliminarValorParametroNova(id_rpv) {
  return await post("eliminarValorParametroNova", {
    id_rpv,
  });
}


/* ================= GENERAR REPORTE ================= */

export async function generarReporteNova(id_rpt, filtros = {}) {
  return await post("generarReporteNova", {
    id_rpt,
    filtros,
  });
}