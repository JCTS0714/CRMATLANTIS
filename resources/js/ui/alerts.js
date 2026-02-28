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

const CUSTOMER_SERVER_OPTIONS = ['ATLANTIS ONLINE', 'ATLANTIS VIP', 'ATLANTIS POS', 'ATLANTIS FAST', 'LORITO'];
const CUSTOMER_MENBRESIA_OPTIONS = ['Mensual', 'Trimestral', 'Semestral', 'Anual'];
const CONTACT_MONTH_OPTIONS = [
  { value: 1, label: 'Enero' },
  { value: 2, label: 'Febrero' },
  { value: 3, label: 'Marzo' },
  { value: 4, label: 'Abril' },
  { value: 5, label: 'Mayo' },
  { value: 6, label: 'Junio' },
  { value: 7, label: 'Julio' },
  { value: 8, label: 'Agosto' },
  { value: 9, label: 'Septiembre' },
  { value: 10, label: 'Octubre' },
  { value: 11, label: 'Noviembre' },
  { value: 12, label: 'Diciembre' },
];

let localCustomerAutofillModulePromise = null;

function isLocalOnlyAutofillEnabled() {
  if (typeof window === 'undefined') return false;
  const host = window.location.hostname;
  return (
    import.meta.env.DEV ||
    host === 'localhost' ||
    host === '127.0.0.1' ||
    host === '::1'
  );
}

function localAutofillControlMarkup(buttonId = 'sw-local-autofill-btn') {
  if (!isLocalOnlyAutofillEnabled()) return '';
  return `
    <div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800 dark:border-amber-800/40 dark:bg-amber-950/20 dark:text-amber-300">
      Solo local: usa el botón <span class="font-semibold">Rellenar test</span> para autocompletar el formulario con datos aleatorios.
      <button id="${buttonId}" type="button" class="ml-2 inline-flex items-center rounded-md border border-amber-300 bg-white px-2 py-1 text-xs font-medium text-amber-800 hover:bg-amber-100 dark:border-amber-700 dark:bg-slate-900 dark:text-amber-300 dark:hover:bg-amber-900/30">Rellenar test</button>
    </div>
  `;
}

async function getLocalCustomerAutofillModule() {
  if (!isLocalOnlyAutofillEnabled()) return null;

  if (!localCustomerAutofillModulePromise) {
    localCustomerAutofillModulePromise = import(
      /* @vite-ignore */ '/resources/js/local/customerModalAutofill.local.js'
    ).catch(() => null);
  }

  return localCustomerAutofillModulePromise;
}

async function bindLocalAutofillButton(buttonId, callback) {
  const button = document.getElementById(buttonId);
  if (!button) return;
  button.addEventListener('click', callback);
}

function buildCustomerServerOptions(selectedValue = '') {
  const selected = String(selectedValue ?? '').trim();
  const options = [
    `<option value="" ${selected ? '' : 'selected'}>(opcional)</option>`,
    ...CUSTOMER_SERVER_OPTIONS.map((option) =>
      `<option value="${option}" ${selected === option ? 'selected' : ''}>${option}</option>`
    ),
  ];

  return options.join('');
}

function buildCustomerMenbresiaOptions(selectedValue = '') {
  const selected = String(selectedValue ?? '').trim();
  const options = [
    `<option value="" ${selected ? '' : 'selected'}>(opcional)</option>`,
    ...CUSTOMER_MENBRESIA_OPTIONS.map((option) =>
      `<option value="${option}" ${selected === option ? 'selected' : ''}>${option}</option>`
    ),
  ];

  return options.join('');
}

function buildContactMonthOptions(selectedValue = '') {
  const selected = Number.parseInt(String(selectedValue ?? ''), 10);
  const options = [
    `<option value="" ${Number.isInteger(selected) ? '' : 'selected'}>(mes)</option>`,
    ...CONTACT_MONTH_OPTIONS.map((month) =>
      `<option value="${month.value}" ${selected === month.value ? 'selected' : ''}>${month.label}</option>`
    ),
  ];

  return options.join('');
}

export async function promptCustomerEdit(customer = {}) {
  const normalizedDocumentNumber = (customer.document_number ?? '').trim();
  const normalizedPrecio = customer.precio ?? '';
  const normalizedRubro = customer.rubro ?? '';
  const normalizedObservacion = customer.observacion ?? '';

  const html = `
    <div class="w-full max-h-[70vh] overflow-y-auto pr-1 text-left">
      <div class="text-xs text-gray-500 dark:text-slate-400">Los campos con <span class="font-semibold text-red-500">*</span> son obligatorios.</div>

      <div class="mt-3 grid w-full grid-cols-2 gap-4">
        <div class="w-full">
          <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Comercio <span class="text-red-500">*</span></label>
          <input id="sw-cust-company_name" class="mt-1 block w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100" value="${escapeHtml(customer.company_name)}" />
        </div>
        <div class="w-full">
          <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Rubro</label>
          <input id="sw-cust-rubro" class="mt-1 block w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100" value="${escapeHtml(normalizedRubro)}" />
        </div>

        <div class="w-full">
          <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Contacto <span class="text-red-500">*</span></label>
          <input id="sw-cust-contact_name" class="mt-1 block w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100" value="${escapeHtml(customer.contact_name)}" />
        </div>
        <div class="w-full">
          <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Año <span class="text-red-500">*</span></label>
          <input id="sw-cust-fecha_contacto_anio" type="number" min="2000" max="2100" class="mt-1 block w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100" value="${escapeHtml(customer.fecha_contacto_anio)}" />
        </div>

        <div class="w-full">
          <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Celular <span class="text-red-500">*</span></label>
          <input id="sw-cust-contact_phone" class="mt-1 block w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100" value="${escapeHtml(customer.contact_phone)}" />
        </div>
        <div class="w-full">
          <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Mes <span class="text-red-500">*</span></label>
          <select id="sw-cust-fecha_contacto_mes" class="mt-1 block w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100">
            ${buildContactMonthOptions(customer.fecha_contacto_mes)}
          </select>
        </div>

        <div class="w-full">
          <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Ciudad</label>
          <input id="sw-cust-company_address" class="mt-1 block w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100" value="${escapeHtml(customer.company_address)}" />
        </div>
        <div class="w-full">
          <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Link</label>
          <input id="sw-cust-link" class="mt-1 block w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100" value="${escapeHtml(customer.link)}" />
        </div>

        <div class="w-full">
          <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Precio <span class="text-red-500">*</span></label>
          <input id="sw-cust-precio" type="number" step="0.01" min="0" class="mt-1 block w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100" value="${escapeHtml(normalizedPrecio)}" />
        </div>
        <div class="w-full">
          <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Usuario</label>
          <input id="sw-cust-usuario" class="mt-1 block w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100" value="${escapeHtml(customer.usuario)}" />
        </div>

        <div class="w-full">
          <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">RUC <span class="text-red-500">*</span></label>
          <input id="sw-cust-document_number" class="mt-1 block w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100" maxlength="11" value="${escapeHtml(normalizedDocumentNumber)}" />
          <div class="mt-1 text-xs text-gray-500 dark:text-slate-400">Exactamente 11 dígitos numéricos</div>
        </div>
        <div class="w-full">
          <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Contraseña</label>
          <input id="sw-cust-contrasena" class="mt-1 block w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100" value="${escapeHtml(customer.contrasena)}" />
        </div>

        <div class="w-full col-span-2">
          <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Servidor</label>
          <select id="sw-cust-servidor" class="mt-1 block w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100">
            ${buildCustomerServerOptions(customer.servidor)}
          </select>
        </div>

        <div class="w-full col-span-2">
          <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Menbresia</label>
          <select id="sw-cust-menbresia" class="mt-1 block w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100">
            ${buildCustomerMenbresiaOptions(customer.menbresia)}
          </select>
        </div>

        <div class="w-full col-span-2">
          <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Observación</label>
          <textarea id="sw-cust-observacion" rows="3" class="mt-1 block w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100" placeholder="Observaciones del cliente...">${escapeHtml(normalizedObservacion)}</textarea>
        </div>
      </div>

      <input id="sw-cust-name" type="hidden" value="${escapeHtml(customer.name)}" />
      <input id="sw-cust-contact_email" type="hidden" value="${escapeHtml(customer.contact_email)}" />
      <input id="sw-cust-document_type" type="hidden" value="ruc" />
    </div>
  `;

  const res = await modalSwal.fire({
    title: 'Editar Cliente Postventa',
    html,
    width: '980px',
    focusConfirm: false,
    showCancelButton: true,
    confirmButtonText: 'Guardar',
    cancelButtonText: 'Cancelar',
    preConfirm: () => {
      const contact_name = document.getElementById('sw-cust-contact_name')?.value?.trim() ?? '';
      const company_name = document.getElementById('sw-cust-company_name')?.value?.trim() ?? '';
      const contact_phone = document.getElementById('sw-cust-contact_phone')?.value?.trim() ?? '';
      const company_address = document.getElementById('sw-cust-company_address')?.value?.trim() ?? '';
      const precioRaw = document.getElementById('sw-cust-precio')?.value?.trim() ?? '';
      const rubro = document.getElementById('sw-cust-rubro')?.value?.trim() ?? '';
      const link = document.getElementById('sw-cust-link')?.value?.trim() ?? '';
      const usuario = document.getElementById('sw-cust-usuario')?.value?.trim() ?? '';
      const contrasena = document.getElementById('sw-cust-contrasena')?.value?.trim() ?? '';
      const servidor = document.getElementById('sw-cust-servidor')?.value?.trim() ?? '';
      const menbresia = document.getElementById('sw-cust-menbresia')?.value?.trim() ?? '';
      const observacion = document.getElementById('sw-cust-observacion')?.value?.trim() ?? '';
      const fecha_contacto_mes = document.getElementById('sw-cust-fecha_contacto_mes')?.value?.trim() ?? '';
      const fecha_contacto_anio = document.getElementById('sw-cust-fecha_contacto_anio')?.value?.trim() ?? '';
      const document_number = document.getElementById('sw-cust-document_number')?.value?.trim() ?? '';
      const precioNormalized = precioRaw.replace(',', '.');
      const precio = Number.parseFloat(precioNormalized);

      const name = contact_name || company_name;

      if (!company_name) {
        Swal.showValidationMessage('El comercio es requerido.');
        return false;
      }

      if (!contact_name) {
        Swal.showValidationMessage('El contacto es requerido.');
        return false;
      }

      if (!contact_phone) {
        Swal.showValidationMessage('El celular es requerido.');
        return false;
      }

      if (!precioRaw || Number.isNaN(precio) || precio < 0) {
        Swal.showValidationMessage('El precio es requerido y debe ser válido.');
        return false;
      }

      if (servidor && !CUSTOMER_SERVER_OPTIONS.includes(servidor)) {
        Swal.showValidationMessage('El servidor debe ser ATLANTIS ONLINE, ATLANTIS VIP o ATLANTIS POS.');
        return false;
      }

      if (menbresia && !CUSTOMER_MENBRESIA_OPTIONS.includes(menbresia)) {
        Swal.showValidationMessage('La menbresia debe ser Mensual, Anual, Trimestral o Semestral.');
        return false;
      }

      if (!fecha_contacto_mes || !fecha_contacto_anio) {
        Swal.showValidationMessage('Mes y año son obligatorios.');
        return false;
      }

      const mesNumber = Number.parseInt(fecha_contacto_mes, 10);
      const anioNumber = Number.parseInt(fecha_contacto_anio, 10);

      if (!Number.isInteger(mesNumber) || mesNumber < 1 || mesNumber > 12) {
        Swal.showValidationMessage('El mes de contacto debe estar entre 1 y 12.');
        return false;
      }

      if (!Number.isInteger(anioNumber) || anioNumber < 2000 || anioNumber > 2100) {
        Swal.showValidationMessage('El año de contacto no es válido.');
        return false;
      }

      if (!/^\d{11}$/.test(document_number)) {
        Swal.showValidationMessage('El RUC es obligatorio y debe tener 11 dígitos.');
        return false;
      }

      return {
        name,
        contact_name: contact_name || null,
        company_name: company_name || null,
        contact_phone: contact_phone || null,
        contact_email: customer.contact_email || null,
        company_address: company_address || null,
        precio,
        rubro: rubro || null,
        mes: String(mesNumber),
        link: link || null,
        usuario: usuario || null,
        contrasena: contrasena || null,
        servidor: servidor || null,
        menbresia: menbresia || null,
        observacion: observacion || null,
        fecha_contacto_mes: mesNumber,
        fecha_contacto_anio: anioNumber,
        document_type: 'ruc',
        document_number: document_number || null,
      };
    },
  });

  return res.isConfirmed ? res.value : null;
}

export async function promptCustomerCreate() {
  const empty = {
    contact_name: '',
    company_name: '',
    contact_phone: '',
    company_address: '',
    precio: '',
    rubro: '',
    link: '',
    usuario: '',
    contrasena: '',
    servidor: '',
    menbresia: 'Mensual',
    observacion: '',
    fecha_contacto_mes: String(new Date().getMonth() + 1),
    fecha_contacto_anio: String(new Date().getFullYear()),
    document_number: '',
  };

  const html = `
    <div class="w-full max-h-[70vh] overflow-y-auto pr-1 text-left">
      <div class="text-xs text-gray-500 dark:text-slate-400">Los campos con <span class="font-semibold text-red-500">*</span> son obligatorios.</div>
      <div class="mt-2">${localAutofillControlMarkup('sw-cust-autofill-create-local')}</div>

      <div class="mt-3 grid w-full grid-cols-2 gap-4">
        <div class="w-full">
          <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Comercio <span class="text-red-500">*</span></label>
          <input id="sw-cust-company_name" class="mt-1 block w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100" value="${escapeHtml(empty.company_name)}" />
        </div>
        <div class="w-full">
          <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Rubro</label>
          <input id="sw-cust-rubro" class="mt-1 block w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100" value="${escapeHtml(empty.rubro)}" />
        </div>

        <div class="w-full">
          <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Contacto <span class="text-red-500">*</span></label>
          <input id="sw-cust-contact_name" class="mt-1 block w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100" value="${escapeHtml(empty.contact_name)}" />
        </div>
        <div class="w-full">
          <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Año <span class="text-red-500">*</span></label>
          <input id="sw-cust-fecha_contacto_anio" type="number" min="2000" max="2100" class="mt-1 block w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100" value="${escapeHtml(empty.fecha_contacto_anio)}" />
        </div>

        <div class="w-full">
          <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Celular <span class="text-red-500">*</span></label>
          <input id="sw-cust-contact_phone" class="mt-1 block w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100" value="${escapeHtml(empty.contact_phone)}" />
        </div>
        <div class="w-full">
          <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Mes <span class="text-red-500">*</span></label>
          <select id="sw-cust-fecha_contacto_mes" class="mt-1 block w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100">
            ${buildContactMonthOptions(empty.fecha_contacto_mes)}
          </select>
        </div>

        <div class="w-full">
          <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Ciudad</label>
          <input id="sw-cust-company_address" class="mt-1 block w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100" value="${escapeHtml(empty.company_address)}" />
        </div>
        <div class="w-full">
          <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Link</label>
          <input id="sw-cust-link" class="mt-1 block w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100" value="${escapeHtml(empty.link)}" />
        </div>

        <div class="w-full">
          <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Precio <span class="text-red-500">*</span></label>
          <input id="sw-cust-precio" type="number" step="0.01" min="0" class="mt-1 block w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100" value="${escapeHtml(empty.precio)}" />
        </div>
        <div class="w-full">
          <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Usuario</label>
          <input id="sw-cust-usuario" class="mt-1 block w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100" value="${escapeHtml(empty.usuario)}" />
        </div>

        <div class="w-full">
          <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">RUC <span class="text-red-500">*</span></label>
          <input id="sw-cust-document_number" class="mt-1 block w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100" maxlength="11" value="${escapeHtml(empty.document_number)}" />
          <div class="mt-1 text-xs text-gray-500 dark:text-slate-400">Exactamente 11 dígitos numéricos</div>
        </div>
        <div class="w-full">
          <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Contraseña</label>
          <input id="sw-cust-contrasena" class="mt-1 block w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100" value="${escapeHtml(empty.contrasena)}" />
        </div>

        <div class="w-full col-span-2">
          <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Servidor</label>
          <select id="sw-cust-servidor" class="mt-1 block w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100">
            ${buildCustomerServerOptions(empty.servidor)}
          </select>
        </div>

        <div class="w-full col-span-2">
          <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Menbresia</label>
          <select id="sw-cust-menbresia" class="mt-1 block w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100">
            ${buildCustomerMenbresiaOptions(empty.menbresia)}
          </select>
        </div>

        <div class="w-full col-span-2">
          <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Observación</label>
          <textarea id="sw-cust-observacion" rows="3" class="mt-1 block w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100" placeholder="Observaciones del cliente...">${escapeHtml(empty.observacion)}</textarea>
        </div>
      </div>

      <input id="sw-cust-name" type="hidden" value="" />
      <input id="sw-cust-contact_email" type="hidden" value="" />
      <input id="sw-cust-document_type" type="hidden" value="ruc" />
    </div>
  `;

  const res = await modalSwal.fire({
    title: 'Agregar Cliente Postventa',
    html,
    width: '980px',
    focusConfirm: false,
    showCancelButton: true,
    confirmButtonText: 'Crear',
    cancelButtonText: 'Cancelar',
    didOpen: async () => {
      const module = await getLocalCustomerAutofillModule();
      if (!module?.autofillCustomerModalForm) return;
      await bindLocalAutofillButton('sw-cust-autofill-create-local', () => module.autofillCustomerModalForm());
    },
    preConfirm: () => {
      const contact_name = document.getElementById('sw-cust-contact_name')?.value?.trim() ?? '';
      const company_name = document.getElementById('sw-cust-company_name')?.value?.trim() ?? '';
      const contact_phone = document.getElementById('sw-cust-contact_phone')?.value?.trim() ?? '';
      const company_address = document.getElementById('sw-cust-company_address')?.value?.trim() ?? '';
      const precioRaw = document.getElementById('sw-cust-precio')?.value?.trim() ?? '';
      const rubro = document.getElementById('sw-cust-rubro')?.value?.trim() ?? '';
      const link = document.getElementById('sw-cust-link')?.value?.trim() ?? '';
      const usuario = document.getElementById('sw-cust-usuario')?.value?.trim() ?? '';
      const contrasena = document.getElementById('sw-cust-contrasena')?.value?.trim() ?? '';
      const servidor = document.getElementById('sw-cust-servidor')?.value?.trim() ?? '';
      const menbresia = document.getElementById('sw-cust-menbresia')?.value?.trim() ?? '';
      const observacion = document.getElementById('sw-cust-observacion')?.value?.trim() ?? '';
      const fecha_contacto_mes = document.getElementById('sw-cust-fecha_contacto_mes')?.value?.trim() ?? '';
      const fecha_contacto_anio = document.getElementById('sw-cust-fecha_contacto_anio')?.value?.trim() ?? '';
      const document_number = document.getElementById('sw-cust-document_number')?.value?.trim() ?? '';
      const precioNormalized = precioRaw.replace(',', '.');
      const precio = Number.parseFloat(precioNormalized);

      const name = contact_name || company_name;

      if (!company_name) {
        Swal.showValidationMessage('El comercio es requerido.');
        return false;
      }
      if (!contact_name) {
        Swal.showValidationMessage('El contacto es requerido.');
        return false;
      }
      if (!contact_phone) {
        Swal.showValidationMessage('El celular es requerido.');
        return false;
      }
      if (!precioRaw || Number.isNaN(precio) || precio < 0) {
        Swal.showValidationMessage('El precio es requerido y debe ser válido.');
        return false;
      }
      if (!/^\d{11}$/.test(document_number)) {
        Swal.showValidationMessage('El RUC es obligatorio y debe tener 11 dígitos.');
        return false;
      }

      if (!fecha_contacto_mes || !fecha_contacto_anio) {
        Swal.showValidationMessage('Mes y año son obligatorios.');
        return false;
      }

      if (servidor && !CUSTOMER_SERVER_OPTIONS.includes(servidor)) {
        Swal.showValidationMessage('El servidor debe ser ATLANTIS ONLINE, ATLANTIS VIP o ATLANTIS POS.');
        return false;
      }

      if (menbresia && !CUSTOMER_MENBRESIA_OPTIONS.includes(menbresia)) {
        Swal.showValidationMessage('La menbresia debe ser Mensual, Anual, Trimestral o Semestral.');
        return false;
      }

      const mesNumber = Number.parseInt(fecha_contacto_mes, 10);
      const anioNumber = Number.parseInt(fecha_contacto_anio, 10);

      if (!Number.isInteger(mesNumber) || mesNumber < 1 || mesNumber > 12) {
        Swal.showValidationMessage('El mes de contacto debe estar entre 1 y 12.');
        return false;
      }

      if (!Number.isInteger(anioNumber) || anioNumber < 2000 || anioNumber > 2100) {
        Swal.showValidationMessage('El año de contacto no es válido.');
        return false;
      }

      return {
        name,
        contact_name: contact_name || null,
        company_name: company_name || null,
        contact_phone: contact_phone || null,
        contact_email: null,
        company_address: company_address || null,
        precio,
        rubro: rubro || null,
        mes: String(mesNumber),
        link: link || null,
        usuario: usuario || null,
        contrasena: contrasena || null,
        servidor: servidor || null,
        menbresia: menbresia || null,
        observacion: observacion || null,
        fecha_contacto_mes: mesNumber,
        fecha_contacto_anio: anioNumber,
        document_type: 'ruc',
        document_number: document_number || null,
      };
    },
  });

  return res.isConfirmed ? res.value : null;
}

export async function promptCustomerPaymentDate(customer = {}) {
  const today = new Date();
  const yyyy = today.getFullYear();
  const mm = String(today.getMonth() + 1).padStart(2, '0');
  const dd = String(today.getDate()).padStart(2, '0');
  const defaultDate = `${yyyy}-${mm}-${dd}`;

  const html = `
    <div class="grid gap-3 text-left">
      <div class="text-xs text-gray-600 dark:text-slate-300">
        Cliente: <span class="font-medium text-gray-900 dark:text-slate-100">${escapeHtml(customer.company_name || customer.name || 'Sin nombre')}</span>
      </div>

      <div>
        <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Fecha de pago</label>
        <input id="sw-pay-date" type="date" class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100" value="${defaultDate}" />
      </div>

      <div>
        <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Detalle (opcional)</label>
        <textarea id="sw-pay-note" rows="3" class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100" placeholder="Ejemplo: pago mensual, renovación, monto, etc."></textarea>
      </div>
    </div>
  `;

  const res = await modalSwal.fire({
    title: 'Agregar fecha de pago',
    html,
    focusConfirm: false,
    showCancelButton: true,
    confirmButtonText: 'Agregar al calendario',
    cancelButtonText: 'Cancelar',
    preConfirm: () => {
      const date = document.getElementById('sw-pay-date')?.value?.trim() ?? '';
      const note = document.getElementById('sw-pay-note')?.value?.trim() ?? '';

      if (!date) {
        Swal.showValidationMessage('La fecha de pago es requerida.');
        return false;
      }

      return {
        date,
        note: note || null,
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
      ${isCreate ? localAutofillControlMarkup('sw-cont-autofill-create-local') : ''}
      <div class="grid grid-cols-1 gap-3 ${isCreate ? '' : 'sm:grid-cols-2'}">
        <div${isCreate ? ' style="display: none;"' : ''}>
          <label class="block text-xs font-medium text-gray-600 dark:text-slate-300">Nro</label>
          <input id="sw-cont-nro" class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100" value="${escapeHtml(contador.nro)}"${!isCreate ? ' readonly' : ''} />
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
        <label class="block text-xs font-medium text-gray-600 dark:text-slate-300 mb-2">Clientes</label>
        <div id="sw-customers-container">
          <div class="customer-search-group mb-3" data-index="0">
            <div class="flex gap-2">
              <div class="flex-1 relative">
                <input 
                  type="text" 
                  class="customer-search-input w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100" 
                  placeholder="Buscar cliente por nombre, empresa o documento..." 
                  autocomplete="off"
                />
                <div class="customer-dropdown absolute z-50 w-full mt-1 bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-lg shadow-lg hidden max-h-48 overflow-y-auto"></div>
                <input type="hidden" class="customer-id-input" />
              </div>
              <button type="button" class="add-customer-btn px-3 py-2 text-sm bg-green-600 text-white rounded-lg hover:bg-green-700 focus:ring-4 focus:ring-green-300">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
              </button>
              <button type="button" class="remove-customer-btn px-3 py-2 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700 focus:ring-4 focus:ring-red-300 hidden">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
              </button>
            </div>
            <div class="selected-customer mt-2 hidden p-2 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded text-sm"></div>
          </div>
        </div>
        <div class="mt-1 text-xs text-gray-500 dark:text-slate-400">Busca y selecciona los clientes para asignar al contador.</div>
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
    didOpen: async (popup) => {
      initCustomerSearch(popup, contador.customers || []);

      if (!isCreate) return;
      const module = await getLocalCustomerAutofillModule();
      if (!module?.autofillContadorCreateModalForm) return;
      await bindLocalAutofillButton('sw-cont-autofill-create-local', () => module.autofillContadorCreateModalForm());
    },
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

      // Obtener IDs de clientes seleccionados
      const customerIds = [];
      document.querySelectorAll('.customer-id-input').forEach(input => {
        const id = input.value?.trim();
        if (id && !customerIds.includes(parseInt(id))) {
          customerIds.push(parseInt(id));
        }
      });

      if (isCreate) {
        // En modo crear, solo requerir comercio o nombre del contador
        if (!comercio && !nom_contador) {
          Swal.showValidationMessage('Completa al menos Comercio o Nombre del contador.');
          return false;
        }
      } else {
        // En modo editar, mantener la validación original
        if (!nro && !comercio && !nom_contador) {
          Swal.showValidationMessage('Completa al menos Nro, Comercio o Nombre del contador.');
          return false;
        }
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
        customer_ids: customerIds,
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

function normalizeDateInputValue(value) {
  if (!value) return '';

  const text = String(value).trim();
  if (text === '') return '';

  const isoMatch = text.match(/^(\d{4}-\d{2}-\d{2})/);
  if (isoMatch) return isoMatch[1];

  const shortMatch = text.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
  if (shortMatch) {
    return `${shortMatch[3]}-${shortMatch[2]}-${shortMatch[1]}`;
  }

  const date = new Date(text);
  if (Number.isNaN(date.getTime())) return '';

  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
}

export async function promptCertificadoEdit(cert = {}, isCreate = false) {
  const fechaCreacionValue = normalizeDateInputValue(cert.fecha_creacion);
  const fechaVencimientoValue = normalizeDateInputValue(cert.fecha_vencimiento);

  const html = `
    <div class="grid gap-3 text-left">
      ${isCreate ? localAutofillControlMarkup('sw-cert-autofill-create-local') : ''}
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
          <input id="sw-cert-fecha_vencimiento" type="date" class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100" value="${escapeHtml(fechaVencimientoValue)}" />
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
        <input id="sw-cert-fecha_creacion" type="date" class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100" value="${escapeHtml(fechaCreacionValue)}" />
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
    didOpen: async () => {
      if (!isCreate) return;
      const module = await getLocalCustomerAutofillModule();
      if (!module?.autofillCertificadoCreateModalForm) return;
      await bindLocalAutofillButton('sw-cert-autofill-create-local', () => module.autofillCertificadoCreateModalForm());
    },
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

// Función para inicializar la búsqueda de clientes
function initCustomerSearch(popup, existingCustomers = []) {
  const container = popup.querySelector('#sw-customers-container');
  let customerCounter = 0;
  
  // Pre-llenar con clientes existentes
  if (existingCustomers.length > 0) {
    container.innerHTML = ''; // Limpiar
    existingCustomers.forEach((customer, index) => {
      addCustomerSearchGroup(container, customer, index);
      customerCounter = index + 1;
    });
    updateRemoveButtons();
  }

  // Event listeners para agregar/quitar campos
  container.addEventListener('click', (e) => {
    if (e.target.closest('.add-customer-btn')) {
      addCustomerSearchGroup(container, null, customerCounter++);
      updateRemoveButtons();
    } else if (e.target.closest('.remove-customer-btn')) {
      e.target.closest('.customer-search-group').remove();
      updateRemoveButtons();
    }
  });

  // Event listener para búsqueda
  let searchTimeout;
  container.addEventListener('input', (e) => {
    if (e.target.classList.contains('customer-search-input')) {
      clearTimeout(searchTimeout);
      searchTimeout = setTimeout(() => searchCustomers(e.target), 300);
    }
  });

  // Cerrar dropdowns al hacer clic fuera
  document.addEventListener('click', (e) => {
    if (!e.target.closest('.customer-search-group')) {
      popup.querySelectorAll('.customer-dropdown').forEach(dropdown => {
        dropdown.classList.add('hidden');
      });
    }
  });

  function addCustomerSearchGroup(container, customer = null, index = 0) {
    const group = document.createElement('div');
    group.className = 'customer-search-group mb-3';
    group.setAttribute('data-index', index);
    
    const customerName = customer ? `${customer.name}${customer.company_name ? ' - ' + customer.company_name : ''}` : '';
    const customerId = customer ? customer.id : '';
    
    group.innerHTML = `
      <div class="flex gap-2">
        <div class="flex-1 relative">
          <input 
            type="text" 
            class="customer-search-input w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100" 
            placeholder="Buscar cliente por nombre, empresa o documento..." 
            autocomplete="off"
            value="${escapeHtml(customerName)}"
          />
          <div class="customer-dropdown absolute z-50 w-full mt-1 bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-lg shadow-lg hidden max-h-48 overflow-y-auto"></div>
          <input type="hidden" class="customer-id-input" value="${customerId}" />
        </div>
        <button type="button" class="add-customer-btn px-3 py-2 text-sm bg-green-600 text-white rounded-lg hover:bg-green-700 focus:ring-4 focus:ring-green-300">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
        </button>
        <button type="button" class="remove-customer-btn px-3 py-2 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700 focus:ring-4 focus:ring-red-300 hidden">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
      </div>
      <div class="selected-customer mt-2 ${customer ? '' : 'hidden'} p-2 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded text-sm">
        ${customer ? `<strong>${escapeHtml(customer.name)}</strong>${customer.company_name ? ' - ' + escapeHtml(customer.company_name) : ''}<br><small class="text-gray-600">${customer.document_number || 'Sin documento'}</small>` : ''}
      </div>
    `;
    
    container.appendChild(group);
  }

  function updateRemoveButtons() {
    const groups = container.querySelectorAll('.customer-search-group');
    groups.forEach((group, index) => {
      const removeBtn = group.querySelector('.remove-customer-btn');
      removeBtn.classList.toggle('hidden', groups.length === 1);
    });
  }

  async function searchCustomers(input) {
    const query = input.value.trim();
    const dropdown = input.nextElementSibling;
    
    if (query.length < 2) {
      dropdown.classList.add('hidden');
      return;
    }

    try {
      const response = await fetch(`/customers/search?q=${encodeURIComponent(query)}`);
      const data = await response.json();
      
      dropdown.innerHTML = '';
      
      if (data.customers && data.customers.length > 0) {
        data.customers.forEach(customer => {
          const item = document.createElement('div');
          item.className = 'p-3 hover:bg-gray-50 dark:hover:bg-slate-800 cursor-pointer border-b border-gray-100 dark:border-slate-700 last:border-b-0';
          item.innerHTML = `
            <div class="font-medium text-gray-900 dark:text-slate-100">${escapeHtml(customer.name)}</div>
            ${customer.company_name ? `<div class="text-sm text-gray-600 dark:text-slate-400">${escapeHtml(customer.company_name)}</div>` : ''}
            <div class="text-xs text-gray-500 dark:text-slate-500">${customer.document_number || 'Sin documento'}</div>
          `;
          
          item.addEventListener('click', () => {
            selectCustomer(input, customer);
          });
          
          dropdown.appendChild(item);
        });
        
        dropdown.classList.remove('hidden');
      } else {
        dropdown.innerHTML = '<div class="p-3 text-gray-500 dark:text-slate-400 text-sm">No se encontraron clientes</div>';
        dropdown.classList.remove('hidden');
      }
    } catch (error) {
      console.error('Error searching customers:', error);
      dropdown.classList.add('hidden');
    }
  }

  function selectCustomer(input, customer) {
    const group = input.closest('.customer-search-group');
    const hiddenInput = group.querySelector('.customer-id-input');
    const selectedDiv = group.querySelector('.selected-customer');
    const dropdown = input.nextElementSibling;
    
    input.value = `${customer.name}${customer.company_name ? ' - ' + customer.company_name : ''}`;
    hiddenInput.value = customer.id;
    
    selectedDiv.innerHTML = `
      <strong>${escapeHtml(customer.name)}</strong>${customer.company_name ? ' - ' + escapeHtml(customer.company_name) : ''}<br>
      <small class="text-gray-600">${customer.document_number || 'Sin documento'}</small>
    `;
    selectedDiv.classList.remove('hidden');
    
    dropdown.classList.add('hidden');
  }
}
