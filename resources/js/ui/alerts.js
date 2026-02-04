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
    didOpen: (popup) => {
      initCustomerSearch(popup, contador.customers || []);
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
