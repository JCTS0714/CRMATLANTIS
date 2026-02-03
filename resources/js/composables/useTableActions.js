import axios from 'axios';
import Swal from 'sweetalert2';

/**
 * Generic CRUD actions for table operations
 */
export class TableActions {
  constructor(baseEndpoint) {
    this.baseEndpoint = baseEndpoint;
  }

  // Basic CRUD operations
  async create(data) {
    try {
      const response = await axios.post(this.baseEndpoint, data);
      return response.data;
    } catch (error) {
      throw error;
    }
  }

  async update(id, data) {
    try {
      const response = await axios.patch(`${this.baseEndpoint}/${id}`, data);
      return response.data;
    } catch (error) {
      throw error;
    }
  }

  async delete(id) {
    try {
      const response = await axios.delete(`${this.baseEndpoint}/${id}`);
      return response.data;
    } catch (error) {
      throw error;
    }
  }

  async archive(id) {
    try {
      const response = await axios.patch(`${this.baseEndpoint}/${id}/archive`);
      return response.data;
    } catch (error) {
      throw error;
    }
  }

  async restore(id) {
    try {
      const response = await axios.patch(`${this.baseEndpoint}/${id}/restore`);
      return response.data;
    } catch (error) {
      throw error;
    }
  }

  // Helper function to get dark mode styles
  getSweetAlertTheme() {
    const isDark = document.documentElement.classList.contains('dark');
    
    return isDark ? {
      background: '#0f172a', // slate-900
      color: '#f1f5f9', // slate-100
      confirmButtonColor: '#ef4444',
      cancelButtonColor: '#64748b',
    } : {
      background: '#ffffff',
      color: '#1e293b',
      confirmButtonColor: '#ef4444',
      cancelButtonColor: '#6b7280',
    };
  }

  // Confirmation dialogs
  async confirmDelete(itemName, customText = null) {
    const text = customText || `Se eliminará permanentemente "${itemName}"`;
    const theme = this.getSweetAlertTheme();
    
    const result = await Swal.fire({
      title: '¿Eliminar elemento?',
      text,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar',
      confirmButtonColor: theme.confirmButtonColor,
      cancelButtonColor: theme.cancelButtonColor,
      background: theme.background,
      color: theme.color,
      reverseButtons: true
    });

    return result.isConfirmed;
  }

  async confirmArchive(itemName, customText = null) {
    const text = customText || `Se archivará "${itemName}"`;
    const theme = this.getSweetAlertTheme();
    
    const result = await Swal.fire({
      title: '¿Archivar elemento?',
      text,
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Sí, archivar',
      cancelButtonText: 'Cancelar',
      confirmButtonColor: '#f59e0b',
      cancelButtonColor: theme.cancelButtonColor,
      background: theme.background,
      color: theme.color,
      reverseButtons: true
    });

    return result.isConfirmed;
  }

  async confirmBulkAction(action, itemCount) {
    const theme = this.getSweetAlertTheme();
    const actions = {
      delete: {
        title: '¿Eliminar elementos?',
        text: `Se eliminarán ${itemCount} elementos permanentemente`,
        confirmText: 'Sí, eliminar todos',
        color: '#ef4444'
      },
      archive: {
        title: '¿Archivar elementos?',
        text: `Se archivarán ${itemCount} elementos`,
        confirmText: 'Sí, archivar todos',
        color: '#f59e0b'
      }
    };

    const config = actions[action];
    if (!config) return false;

    const result = await Swal.fire({
      title: config.title,
      text: config.text,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: config.confirmText,
      cancelButtonText: 'Cancelar',
      confirmButtonColor: config.color,
      cancelButtonColor: theme.cancelButtonColor,
      background: theme.background,
      color: theme.color,
      reverseButtons: true
    });

    return result.isConfirmed;
  }

  // Success notifications
  showSuccess(message, title = '¡Éxito!') {
    const theme = this.getSweetAlertTheme();
    
    Swal.fire({
      title,
      text: message,
      icon: 'success',
      timer: 2000,
      showConfirmButton: false,
      background: theme.background,
      color: theme.color
    });
  }

  showError(message, title = 'Error') {
    const theme = this.getSweetAlertTheme();
    
    Swal.fire({
      title,
      text: message,
      icon: 'error',
      background: theme.background,
      color: theme.color
    });
  }

  // Bulk operations  
  async bulkDelete(ids) {
    try {
      const promises = ids.map(id => this.delete(id));
      const results = await Promise.allSettled(promises);
      
      const successful = results.filter(r => r.status === 'fulfilled').length;
      const failed = results.filter(r => r.status === 'rejected').length;
      
      if (failed === 0) {
        this.showSuccess(`${successful} elementos eliminados correctamente`);
      } else {
        this.showError(`${successful} elementos eliminados, ${failed} fallaron`);
      }
      
      return { successful, failed };
    } catch (error) {
      throw error;
    }
  }

  async bulkArchive(ids) {
    try {
      const promises = ids.map(id => this.archive(id));
      const results = await Promise.allSettled(promises);
      
      const successful = results.filter(r => r.status === 'fulfilled').length;
      const failed = results.filter(r => r.status === 'rejected').length;
      
      if (failed === 0) {
        this.showSuccess(`${successful} elementos archivados correctamente`);
      } else {
        this.showError(`${successful} elementos archivados, ${failed} fallaron`);
      }
      
      return { successful, failed };
    } catch (error) {
      throw error;
    }
  }
}

/**
 * Factory function to create table actions for specific endpoints
 */
export function createTableActions(endpoint) {
  return new TableActions(endpoint);
}

/**
 * Specific action implementations
 */
export const LeadsActions = createTableActions('/leads');
export const UsersActions = createTableActions('/users');
export const CustomersActions = createTableActions('/customers');
export const RolesActions = createTableActions('/roles');

/**
 * Composable for using table actions with better structure and type-like behavior
 */
export function useTableActions(endpoint) {
  const actions = createTableActions(endpoint);

  const performAction = async (actionName, id, data = null, itemName = '') => {
    switch (actionName) {
      case 'delete':
        if (await actions.confirmDelete(itemName)) {
          await actions.delete(id);
          actions.showSuccess('Elemento eliminado correctamente');
          return true;
        }
        return false;

      case 'archive':
        if (await actions.confirmArchive(itemName)) {
          await actions.archive(id);
          actions.showSuccess('Elemento archivado correctamente');
          return true;
        }
        return false;

      case 'update':
        await actions.update(id, data);
        actions.showSuccess('Elemento actualizado correctamente');
        return true;

      case 'create':
        const result = await actions.create(data);
        actions.showSuccess('Elemento creado correctamente');
        return result;

      default:
        throw new Error(`Action '${actionName}' not supported`);
    }
  };

  const performBulkAction = async (actionName, ids) => {
    if (!ids.length) return false;

    const confirmed = await actions.confirmBulkAction(actionName, ids.length);
    if (!confirmed) return false;

    switch (actionName) {
      case 'delete':
        return await actions.bulkDelete(ids);
      
      case 'archive':
        return await actions.bulkArchive(ids);
        
      default:
        throw new Error(`Bulk action '${actionName}' not supported`);
    }
  };

  return {
    actions,
    performAction,
    performBulkAction
  };
}