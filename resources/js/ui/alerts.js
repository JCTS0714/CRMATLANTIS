import Swal from 'sweetalert2';

const modalSwal = Swal.mixin({
  buttonsStyling: false,
  customClass: {
    popup:
      'rounded-xl border border-gray-200 bg-white text-gray-900 shadow-xl dark:!border-slate-800 dark:!bg-slate-900 dark:!text-slate-100',
    backdrop: 'bg-black/60',
    title: 'text-base font-semibold text-gray-900 dark:text-slate-100',
    htmlContainer: 'text-sm text-gray-600 dark:text-slate-300',
    actions: 'gap-2',
    confirmButton:
      'inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300 disabled:opacity-60',
    cancelButton:
      'inline-flex items-center rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-4 focus:ring-blue-100 disabled:opacity-60 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800',
  },
});

const toastSwal = Swal.mixin({
  toast: true,
  position: 'top-end',
  showConfirmButton: false,
  timer: 2500,
  timerProgressBar: true,
  didOpen: (toast) => {
    toast.addEventListener('mouseenter', Swal.stopTimer);
    toast.addEventListener('mouseleave', Swal.resumeTimer);
  },
  customClass: {
    popup:
      'rounded-xl border border-gray-200 bg-white text-gray-900 shadow-lg dark:!border-slate-800 dark:!bg-slate-900 dark:!text-slate-100',
    title: 'text-sm font-medium',
    htmlContainer: 'text-sm',
  },
});

export async function confirmDialog({
  title,
  text,
  confirmText = 'Aceptar',
  cancelText = 'Cancelar',
  icon = 'question',
} = {}) {
  const res = await modalSwal.fire({
    title,
    text,
    icon,
    showCancelButton: true,
    confirmButtonText: confirmText,
    cancelButtonText: cancelText,
  });

  return res.isConfirmed === true;
}

export function toastSuccess(title) {
  return toastSwal.fire({ icon: 'success', title });
}

export function toastError(title) {
  return toastSwal.fire({ icon: 'error', title });
}

export function toastInfo(title) {
  return toastSwal.fire({ icon: 'info', title });
}

function escapeHtml(value) {
  return String(value ?? '')
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');
}

export async function promptCustomerEdit(customer = {}) {
  const html = `
    <div class="grid gap-3 text-left">
      <div>
        <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Nombre</label>
        <input id="sw-cust-name" class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100" value="${escapeHtml(customer.name)}" />
      </div>

      <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
        <div>
          <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Contacto</label>
          <input id="sw-cust-contact_name" class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100" value="${escapeHtml(customer.contact_name)}" />
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Empresa</label>
          <input id="sw-cust-company_name" class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100" value="${escapeHtml(customer.company_name)}" />
        </div>
      </div>

      <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
        <div>
          <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Teléfono</label>
          <input id="sw-cust-contact_phone" class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100" value="${escapeHtml(customer.contact_phone)}" />
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Email</label>
          <input id="sw-cust-contact_email" type="email" class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100" value="${escapeHtml(customer.contact_email)}" />
        </div>
      </div>

      <div>
        <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Dirección</label>
        <input id="sw-cust-company_address" class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100" value="${escapeHtml(customer.company_address)}" />
      </div>

      <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
        <div>
          <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Tipo doc</label>
          <select id="sw-cust-document_type" class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100">
            <option value="" ${customer.document_type ? '' : 'selected'}>(opcional)</option>
            <option value="dni" ${customer.document_type === 'dni' ? 'selected' : ''}>DNI</option>
            <option value="ruc" ${customer.document_type === 'ruc' ? 'selected' : ''}>RUC</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Número doc</label>
          <input id="sw-cust-document_number" class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100" value="${escapeHtml(customer.document_number)}" />
        </div>
      </div>
    </div>
  `;

  const res = await modalSwal.fire({
    title: 'Editar cliente',
    html,
    focusConfirm: false,
    showCancelButton: true,
    confirmButtonText: 'Guardar',
    cancelButtonText: 'Cancelar',
    preConfirm: () => {
      const name = document.getElementById('sw-cust-name')?.value?.trim() ?? '';
      const contact_name = document.getElementById('sw-cust-contact_name')?.value?.trim() ?? '';
      const company_name = document.getElementById('sw-cust-company_name')?.value?.trim() ?? '';
      const contact_phone = document.getElementById('sw-cust-contact_phone')?.value?.trim() ?? '';
      const contact_email = document.getElementById('sw-cust-contact_email')?.value?.trim() ?? '';
      const company_address = document.getElementById('sw-cust-company_address')?.value?.trim() ?? '';
      const document_type = document.getElementById('sw-cust-document_type')?.value ?? '';
      const document_number = document.getElementById('sw-cust-document_number')?.value?.trim() ?? '';

      if (!name) {
        Swal.showValidationMessage('El nombre es requerido.');
        return false;
      }

      if (document_number && !document_type) {
        Swal.showValidationMessage('El tipo de documento es requerido si envías número de documento.');
        return false;
      }

      if (document_type && !document_number) {
        Swal.showValidationMessage('El número de documento es requerido.');
        return false;
      }

      return {
        name,
        contact_name: contact_name || null,
        company_name: company_name || null,
        contact_phone: contact_phone || null,
        contact_email: contact_email || null,
        company_address: company_address || null,
        document_type: document_type || null,
        document_number: document_number || null,
      };
    },
  });

  return res.isConfirmed ? res.value : null;
}

export async function promptCustomerCreate() {
  const empty = {
    name: '',
    contact_name: '',
    company_name: '',
    contact_phone: '',
    contact_email: '',
    company_address: '',
    document_type: '',
    document_number: '',
  };

  const html = `
    <div class="grid gap-3 text-left">
      <div>
        <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Nombre</label>
        <input id="sw-cust-name" class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100" value="${escapeHtml(empty.name)}" />
      </div>

      <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
        <div>
          <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Contacto</label>
          <input id="sw-cust-contact_name" class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100" value="${escapeHtml(empty.contact_name)}" />
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Empresa</label>
          <input id="sw-cust-company_name" class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100" value="${escapeHtml(empty.company_name)}" />
        </div>
      </div>

      <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
        <div>
          <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Teléfono</label>
          <input id="sw-cust-contact_phone" class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100" value="${escapeHtml(empty.contact_phone)}" />
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Email</label>
          <input id="sw-cust-contact_email" type="email" class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100" value="${escapeHtml(empty.contact_email)}" />
        </div>
      </div>

      <div>
        <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Dirección</label>
        <input id="sw-cust-company_address" class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100" value="${escapeHtml(empty.company_address)}" />
      </div>

      <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
        <div>
          <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Tipo doc</label>
          <select id="sw-cust-document_type" class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100">
            <option value="" selected>(opcional)</option>
            <option value="dni">DNI</option>
            <option value="ruc">RUC</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Número doc</label>
          <input id="sw-cust-document_number" class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100" value="${escapeHtml(empty.document_number)}" />
        </div>
      </div>
    </div>
  `;

  const res = await modalSwal.fire({
    title: 'Crear cliente',
    html,
    focusConfirm: false,
    showCancelButton: true,
    confirmButtonText: 'Crear',
    cancelButtonText: 'Cancelar',
    preConfirm: () => {
      const name = document.getElementById('sw-cust-name')?.value?.trim() ?? '';
      const contact_name = document.getElementById('sw-cust-contact_name')?.value?.trim() ?? '';
      const company_name = document.getElementById('sw-cust-company_name')?.value?.trim() ?? '';
      const contact_phone = document.getElementById('sw-cust-contact_phone')?.value?.trim() ?? '';
      const contact_email = document.getElementById('sw-cust-contact_email')?.value?.trim() ?? '';
      const company_address = document.getElementById('sw-cust-company_address')?.value?.trim() ?? '';
      const document_type = document.getElementById('sw-cust-document_type')?.value ?? '';
      const document_number = document.getElementById('sw-cust-document_number')?.value?.trim() ?? '';

      if (!name) {
        Swal.showValidationMessage('El nombre es requerido.');
        return false;
      }
      if (document_number && !document_type) {
        Swal.showValidationMessage('El tipo de documento es requerido si envías número de documento.');
        return false;
      }
      if (document_type && !document_number) {
        Swal.showValidationMessage('El número de documento es requerido.');
        return false;
      }

      return {
        name,
        contact_name: contact_name || null,
        company_name: company_name || null,
        contact_phone: contact_phone || null,
        contact_email: contact_email || null,
        company_address: company_address || null,
        document_type: document_type || null,
        document_number: document_number || null,
      };
    },
  });

  return res.isConfirmed ? res.value : null;
}

export async function promptContadorCreate() {
  return promptContadorEdit({
    nro: '',
    comercio: '',
    nom_contador: '',
    titular_tlf: '',
    telefono: '',
    telefono_actu: '',
    link: '',
    usuario: '',
    contrasena: '',
    servidor: '',
    customer: null,
  }, true);
}

export async function promptContadorEdit(contador = {}, isCreate = false) {
  const customerId = contador?.customer?.id ?? '';

  const html = `
    <div class="grid gap-3 text-left">
      <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
        <div>
          <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Nro</label>
          <input id="sw-cont-nro" class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100" value="${escapeHtml(contador.nro)}" />
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Comercio</label>
          <input id="sw-cont-comercio" class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100" value="${escapeHtml(contador.comercio)}" />
        </div>
      </div>

      <div>
        <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Nombre del contador</label>
        <input id="sw-cont-nom_contador" class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100" value="${escapeHtml(contador.nom_contador)}" />
      </div>

      <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
        <div>
          <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Titular tlf</label>
          <input id="sw-cont-titular_tlf" class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100" value="${escapeHtml(contador.titular_tlf)}" />
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Teléfono</label>
          <input id="sw-cont-telefono" class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100" value="${escapeHtml(contador.telefono)}" />
        </div>
      </div>

      <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
        <div>
          <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Teléfono act.</label>
          <input id="sw-cont-telefono_actu" class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100" value="${escapeHtml(contador.telefono_actu)}" />
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Servidor</label>
          <input id="sw-cont-servidor" class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100" value="${escapeHtml(contador.servidor)}" />
        </div>
      </div>

      <div>
        <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Link</label>
        <input id="sw-cont-link" class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100" value="${escapeHtml(contador.link)}" />
      </div>

      <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
        <div>
          <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Usuario</label>
          <input id="sw-cont-usuario" class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100" value="${escapeHtml(contador.usuario)}" />
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Contraseña</label>
          <input id="sw-cont-contrasena" class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100" value="${escapeHtml(contador.contrasena)}" />
        </div>
      </div>

      <div>
        <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Cliente ID (opcional)</label>
        <input id="sw-cont-customer_id" inputmode="numeric" class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100" value="${escapeHtml(customerId)}" placeholder="Ej: 12" />
        <div class="mt-1 text-xs text-gray-500 dark:text-slate-400">Si lo dejas vacío, queda sin asignar.</div>
      </div>
    </div>
  `;

  const res = await modalSwal.fire({
    title: isCreate ? 'Crear contador' : 'Editar contador',
    html,
    focusConfirm: false,
    showCancelButton: true,
    confirmButtonText: isCreate ? 'Crear' : 'Guardar',
    cancelButtonText: 'Cancelar',
    preConfirm: () => {
      const nro = document.getElementById('sw-cont-nro')?.value?.trim() ?? '';
      const comercio = document.getElementById('sw-cont-comercio')?.value?.trim() ?? '';
      const nom_contador = document.getElementById('sw-cont-nom_contador')?.value?.trim() ?? '';
      const titular_tlf = document.getElementById('sw-cont-titular_tlf')?.value?.trim() ?? '';
      const telefono = document.getElementById('sw-cont-telefono')?.value?.trim() ?? '';
      const telefono_actu = document.getElementById('sw-cont-telefono_actu')?.value?.trim() ?? '';
      const servidor = document.getElementById('sw-cont-servidor')?.value?.trim() ?? '';
      const link = document.getElementById('sw-cont-link')?.value?.trim() ?? '';
      const usuario = document.getElementById('sw-cont-usuario')?.value?.trim() ?? '';
      const contrasena = document.getElementById('sw-cont-contrasena')?.value?.trim() ?? '';
      const customer_id_raw = document.getElementById('sw-cont-customer_id')?.value?.trim() ?? '';

      if (!nro && !comercio && !nom_contador) {
        Swal.showValidationMessage('Completa al menos Nro, Comercio o Nombre del contador.');
        return false;
      }

      const customer_id = customer_id_raw ? Number(customer_id_raw) : null;
      if (customer_id_raw && (!Number.isInteger(customer_id) || customer_id <= 0)) {
        Swal.showValidationMessage('Cliente ID inválido.');
        return false;
      }

      return {
        nro: nro || null,
        comercio: comercio || null,
        nom_contador: nom_contador || null,
        titular_tlf: titular_tlf || null,
        telefono: telefono || null,
        telefono_actu: telefono_actu || null,
        servidor: servidor || null,
        link: link || null,
        usuario: usuario || null,
        contrasena: contrasena || null,
        customer_id,
      };
    },
  });

  return res.isConfirmed ? res.value : null;
}

export async function promptCertificadoCreate() {
  return promptCertificadoEdit({
    nombre: '',
    ruc: '',
    usuario: '',
    clave: '',
    fecha_creacion: '',
    fecha_vencimiento: '',
    estado: 'activo',
    tipo: '',
    observacion: '',
  }, true);
}

export async function promptCertificadoEdit(cert = {}, isCreate = false) {
  const html = `
    <div class="grid gap-3 text-left">
      <div>
        <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Nombre</label>
        <input id="sw-cert-nombre" class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100" value="${escapeHtml(cert.nombre)}" />
      </div>

      <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
        <div>
          <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">RUC</label>
          <input id="sw-cert-ruc" class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100" value="${escapeHtml(cert.ruc)}" />
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Tipo</label>
          <select id="sw-cert-tipo" class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100">
            <option value="" ${cert.tipo ? '' : 'selected'}>(opcional)</option>
            <option value="OSE" ${cert.tipo === 'OSE' ? 'selected' : ''}>OSE</option>
            <option value="PSE" ${cert.tipo === 'PSE' ? 'selected' : ''}>PSE</option>
          </select>
        </div>
      </div>

      <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
        <div>
          <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Estado</label>
          <select id="sw-cert-estado" class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100">
            <option value="activo" ${cert.estado === 'activo' || !cert.estado ? 'selected' : ''}>Activo</option>
            <option value="inactivo" ${cert.estado === 'inactivo' ? 'selected' : ''}>Inactivo</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Fecha vencimiento</label>
          <input id="sw-cert-fecha_vencimiento" type="date" class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100" value="${escapeHtml(cert.fecha_vencimiento)}" />
        </div>
      </div>

      <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
        <div>
          <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Usuario</label>
          <input id="sw-cert-usuario" class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100" value="${escapeHtml(cert.usuario)}" />
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Clave</label>
          <input id="sw-cert-clave" class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100" value="${escapeHtml(cert.clave)}" />
        </div>
      </div>

      <div>
        <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Fecha creación</label>
        <input id="sw-cert-fecha_creacion" type="date" class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100" value="${escapeHtml(cert.fecha_creacion)}" />
      </div>

      <div>
        <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Imagen</label>
        <input id="sw-cert-imagen" type="file" accept="image/*" class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100" />
        ${cert.imagen ? `<div class="mt-1 text-xs text-gray-500 dark:text-slate-400">Actual: ${escapeHtml(cert.imagen)}</div>` : ''}
      </div>

      <div>
        <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Observación</label>
        <textarea id="sw-cert-observacion" rows="3" class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100">${escapeHtml(cert.observacion)}</textarea>
      </div>
    </div>
  `;

  const res = await modalSwal.fire({
    title: isCreate ? 'Crear certificado' : 'Editar certificado',
    html,
    focusConfirm: false,
    showCancelButton: true,
    confirmButtonText: isCreate ? 'Crear' : 'Guardar',
    cancelButtonText: 'Cancelar',
    preConfirm: () => {
      const nombre = document.getElementById('sw-cert-nombre')?.value?.trim() ?? '';
      const ruc = document.getElementById('sw-cert-ruc')?.value?.trim() ?? '';
      const tipo = document.getElementById('sw-cert-tipo')?.value ?? '';
      const estado = document.getElementById('sw-cert-estado')?.value ?? 'activo';
      const fecha_vencimiento = document.getElementById('sw-cert-fecha_vencimiento')?.value ?? '';
      const usuario = document.getElementById('sw-cert-usuario')?.value?.trim() ?? '';
      const clave = document.getElementById('sw-cert-clave')?.value?.trim() ?? '';
      const fecha_creacion = document.getElementById('sw-cert-fecha_creacion')?.value ?? '';
      const imagenFile = document.getElementById('sw-cert-imagen')?.files?.[0] ?? null;
      const observacion = document.getElementById('sw-cert-observacion')?.value?.trim() ?? '';

      if (!nombre) {
        Swal.showValidationMessage('El nombre es requerido.');
        return false;
      }

      const result = {
        nombre,
        ruc: ruc || null,
        tipo: tipo || null,
        estado: estado || 'activo',
        fecha_vencimiento: fecha_vencimiento || null,
        usuario: usuario || null,
        clave: clave || null,
        fecha_creacion: fecha_creacion || null,
        observacion: observacion || null,
      };

      if (imagenFile) {
        result.imagen = imagenFile;
      }

      return result;
    },
  });

  return res.isConfirmed ? res.value : null;
}
