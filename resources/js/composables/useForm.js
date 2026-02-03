import { reactive, ref, computed } from 'vue';
import axios from 'axios';

/**
 * Composable for managing form state and submission
 * @param {Object} initialData - Initial form data
 * @param {Function} submitFn - Function to call on form submission
 * @param {Object} config - Configuration options
 */
export function useForm(initialData = {}, submitFn = null, config = {}) {
  const {
    resetOnSuccess = true,
    showSuccessMessage = true
  } = config;

  // Reactive state
  const form = reactive({ ...initialData });
  const errors = ref({});
  const submitting = ref(false);
  const submitted = ref(false);
  const success = ref(false);
  const message = ref('');

  // Computed
  const hasErrors = computed(() => Object.keys(errors.value).length > 0);
  const isValid = computed(() => !hasErrors.value);
  const isDirty = computed(() => {
    return JSON.stringify(form) !== JSON.stringify(initialData);
  });

  // Methods
  const setField = (field, value) => {
    form[field] = value;
    // Clear field error when user starts typing
    if (errors.value[field]) {
      delete errors.value[field];
    }
  };

  const setErrors = (newErrors) => {
    errors.value = newErrors || {};
  };

  const setFieldError = (field, error) => {
    errors.value[field] = error;
  };

  const clearErrors = () => {
    errors.value = {};
  };

  const reset = () => {
    Object.assign(form, initialData);
    clearErrors();
    submitting.value = false;
    submitted.value = false;
    success.value = false;
    message.value = '';
  };

  const submit = async (customSubmitFn = null) => {
    const fn = customSubmitFn || submitFn;
    
    if (!fn) {
      console.error('useForm: No submit function provided');
      return false;
    }

    submitting.value = true;
    submitted.value = true;
    success.value = false;
    message.value = '';
    clearErrors();

    try {
      const result = await fn(form);
      
      success.value = true;
      if (showSuccessMessage && result?.message) {
        message.value = result.message;
      }
      
      if (resetOnSuccess) {
        reset();
      }
      
      return result;
    } catch (error) {
      success.value = false;
      
      if (error.response?.status === 422) {
        // Validation errors
        setErrors(error.response.data.errors);
      } else {
        // General error
        message.value = error.response?.data?.message || 'Ha ocurrido un error';
      }
      
      throw error;
    } finally {
      submitting.value = false;
    }
  };

  const patch = async (endpoint, data = null) => {
    const payload = data || form;
    return await axios.patch(endpoint, payload);
  };

  const post = async (endpoint, data = null) => {
    const payload = data || form;
    return await axios.post(endpoint, payload);
  };

  const put = async (endpoint, data = null) => {
    const payload = data || form;
    return await axios.put(endpoint, payload);
  };

  return {
    // State
    form,
    errors,
    submitting,
    submitted,
    success,
    message,
    
    // Computed
    hasErrors,
    isValid,
    isDirty,
    
    // Methods
    setField,
    setErrors,
    setFieldError,
    clearErrors,
    reset,
    submit,
    patch,
    post,
    put
  };
}