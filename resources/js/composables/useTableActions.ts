import axios, { type AxiosResponse } from 'axios';
import Swal from 'sweetalert2';
import type { ApiResponse } from '@/types';

export interface ActionResult {
  successful: number;
  failed: number;
}

export type ActionType = 'create' | 'update' | 'delete' | 'archive' | 'restore';

/**
 * Generic CRUD actions for table operations
 */
export class TableActions {
  private baseEndpoint: string;

  constructor(baseEndpoint: string) {
    this.baseEndpoint = baseEndpoint;
  }

  // Basic CRUD operations
  async create<T = any>(data: Record<string, any>): Promise<T> {
    try {
      const response: AxiosResponse<T> = await axios.post(this.baseEndpoint, data);
      return response.data;
    } catch (error) {
      throw error;
    }
  }

  async update<T = any>(id: string | number, data: Record<string, any>): Promise<T> {
    try {
      const response: AxiosResponse<T> = await axios.patch(`${this.baseEndpoint}/${id}`, data);
      return response.data;
    } catch (error) {
      throw error;
    }
  }

  async delete<T = any>(id: string | number): Promise<T> {
    try {
      const response: AxiosResponse<T> = await axios.delete(`${this.baseEndpoint}/${id}`);
      return response.data;
    } catch (error) {
      throw error;
    }
  }

  async archive<T = any>(id: string | number): Promise<T> {
    try {
      const response: AxiosResponse<T> = await axios.patch(`${this.baseEndpoint}/${id}/archive`);
      return response.data;
    } catch (error) {
      throw error;
    }
  }

  async restore<T = any>(id: string | number): Promise<T> {
    try {
      const response: AxiosResponse<T> = await axios.patch(`${this.baseEndpoint}/${id}/restore`);
      return response.data;
    } catch (error) {
      throw error;
    }
  }

  // Confirmation dialogs
  async confirmDelete(itemName: string, customText?: string): Promise<boolean> {
    const text = customText || `Se eliminará permanentemente "${itemName}"`;
    
    const result = await Swal.fire({
      title: '¿Eliminar elemento?',
      text,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar',
      confirmButtonColor: '#ef4444',
      reverseButtons: true
    });

    return result.isConfirmed;
  }

  async confirmArchive(itemName: string, customText?: string): Promise<boolean> {
    const text = customText || `Se archivará "${itemName}"`;
    
    const result = await Swal.fire({
      title: '¿Archivar elemento?',
      text,
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Sí, archivar',
      cancelButtonText: 'Cancelar',
      confirmButtonColor: '#f59e0b',
      reverseButtons: true
    });

    return result.isConfirmed;
  }

  async confirmBulkAction(action: 'delete' | 'archive', itemCount: number): Promise<boolean> {
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
      reverseButtons: true
    });

    return result.isConfirmed;
  }

  // Success notifications
  showSuccess(message: string, title = '¡Éxito!'): void {
    Swal.fire({
      title,
      text: message,
      icon: 'success',
      timer: 2000,
      showConfirmButton: false
    });
  }

  showError(message: string, title = 'Error'): void {
    Swal.fire({
      title,
      text: message,
      icon: 'error'
    });
  }

  // Bulk operations
  async bulkDelete(ids: Array<string | number>): Promise<ActionResult> {
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

  async bulkArchive(ids: Array<string | number>): Promise<ActionResult> {
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
export function createTableActions(endpoint: string): TableActions {
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
 * Composable for using table actions
 */
export function useTableActions(endpoint: string) {
  const actions = createTableActions(endpoint);

  const performAction = async (
    actionName: ActionType,
    id: string | number | null,
    data: Record<string, any> | null = null,
    itemName = ''
  ): Promise<any> => {
    switch (actionName) {
      case 'delete':
        if (id !== null && await actions.confirmDelete(itemName)) {
          await actions.delete(id);
          actions.showSuccess('Elemento eliminado correctamente');
          return true;
        }
        return false;

      case 'archive':
        if (id !== null && await actions.confirmArchive(itemName)) {
          await actions.archive(id);
          actions.showSuccess('Elemento archivado correctamente');
          return true;
        }
        return false;

      case 'update':
        if (id !== null && data !== null) {
          await actions.update(id, data);
          actions.showSuccess('Elemento actualizado correctamente');
          return true;
        }
        return false;

      case 'create':
        if (data !== null) {
          const result = await actions.create(data);
          actions.showSuccess('Elemento creado correctamente');
          return result;
        }
        return false;

      default:
        throw new Error(`Action '${actionName}' not supported`);
    }
  };

  const performBulkAction = async (
    actionName: 'delete' | 'archive',
    ids: Array<string | number>
  ): Promise<ActionResult | false> => {
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