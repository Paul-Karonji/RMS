import { useState, useEffect } from 'react';
import { Button, Input, Alert } from '../common';

const PROPERTY_TYPES = [
  { value: 'apartment', label: 'Apartment' },
  { value: 'villa', label: 'Villa' },
  { value: 'townhouse', label: 'Townhouse' },
  { value: 'studio', label: 'Studio' },
  { value: 'penthouse', label: 'Penthouse' },
  { value: 'single_family', label: 'Single Family' },
  { value: 'multi_family', label: 'Multi Family' },
];

const PropertyForm = ({
  initialData = {},
  onSubmit,
  isLoading = false,
  error = null,
  submitLabel = 'Submit',
  isEdit = false,
}) => {
  const [formData, setFormData] = useState({
    name: '',
    property_type: 'apartment',
    description: '',
    address_line_1: '',
    address_line_2: '',
    city: '',
    state: '',
    postal_code: '',
    country: 'Kenya',
    total_units: '',
    commission_percentage: '10',
  });

  const [errors, setErrors] = useState({});

  useEffect(() => {
    if (initialData && Object.keys(initialData).length > 0) {
      setFormData({
        name: initialData.property_name || initialData.name || '',
        property_type: initialData.property_type || 'apartment',
        description: initialData.description || '',
        address_line_1: initialData.address || initialData.address_line_1 || '',
        address_line_2: initialData.address_line_2 || '',
        city: initialData.city || '',
        state: initialData.county || initialData.state || '',
        postal_code: initialData.postal_code || '',
        country: initialData.country || 'Kenya',
        total_units: initialData.total_units?.toString() || '',
        commission_percentage: initialData.commission_percentage?.toString() || '10',
      });
    }
  }, [initialData]);

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData((prev) => ({ ...prev, [name]: value }));
    // Clear error when user types
    if (errors[name]) {
      setErrors((prev) => ({ ...prev, [name]: null }));
    }
  };

  const validate = () => {
    const newErrors = {};

    if (!formData.name.trim()) {
      newErrors.name = 'Property name is required';
    } else if (formData.name.length < 3) {
      newErrors.name = 'Property name must be at least 3 characters';
    }

    if (!formData.property_type) {
      newErrors.property_type = 'Property type is required';
    }

    if (!formData.address_line_1.trim()) {
      newErrors.address_line_1 = 'Address is required';
    }

    if (!formData.city.trim()) {
      newErrors.city = 'City is required';
    }

    if (!formData.state.trim()) {
      newErrors.state = 'State/County is required';
    }

    if (!formData.country.trim()) {
      newErrors.country = 'Country is required';
    }

    if (!formData.total_units) {
      newErrors.total_units = 'Total units is required';
    } else if (parseInt(formData.total_units) < 1) {
      newErrors.total_units = 'Total units must be at least 1';
    }

    if (!formData.commission_percentage) {
      newErrors.commission_percentage = 'Commission percentage is required';
    } else if (parseFloat(formData.commission_percentage) < 0) {
      newErrors.commission_percentage = 'Commission cannot be negative';
    } else if (parseFloat(formData.commission_percentage) > 100) {
      newErrors.commission_percentage = 'Commission cannot exceed 100%';
    }

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = (e) => {
    e.preventDefault();

    if (!validate()) return;

    const submitData = {
      name: formData.name,
      property_type: formData.property_type,
      description: formData.description || null,
      address_line_1: formData.address_line_1,
      address_line_2: formData.address_line_2 || null,
      city: formData.city,
      state: formData.state,
      postal_code: formData.postal_code || null,
      country: formData.country,
      total_units: parseInt(formData.total_units),
      commission_percentage: parseFloat(formData.commission_percentage),
    };

    onSubmit(submitData);
  };

  return (
    <form onSubmit={handleSubmit} className="space-y-6">
      {error && (
        <Alert type="error" message={error} />
      )}

      {/* Property Name */}
      <Input
        label="Property Name"
        name="name"
        value={formData.name}
        onChange={handleChange}
        placeholder="e.g., Green Valley Apartments"
        error={errors.name}
        required
      />

      {/* Property Type */}
      <div className="w-full">
        <label className="block text-sm font-medium text-text mb-1.5">
          Property Type <span className="text-red-500">*</span>
        </label>
        <select
          name="property_type"
          value={formData.property_type}
          onChange={handleChange}
          className={`
            w-full px-4 py-3 rounded-lg border transition-all duration-200
            text-text bg-white
            focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary
            ${errors.property_type
              ? 'border-error focus:ring-error focus:border-error'
              : 'border-slate-300 hover:border-slate-400'
            }
          `}
        >
          {PROPERTY_TYPES.map((type) => (
            <option key={type.value} value={type.value}>
              {type.label}
            </option>
          ))}
        </select>
        {errors.property_type && (
          <p className="mt-1.5 text-sm text-error">{errors.property_type}</p>
        )}
      </div>

      {/* Description */}
      <div className="w-full">
        <label className="block text-sm font-medium text-text mb-1.5">
          Description
        </label>
        <textarea
          name="description"
          value={formData.description}
          onChange={handleChange}
          rows={3}
          placeholder="Brief description of the property..."
          className="w-full px-4 py-3 rounded-lg border border-slate-300 hover:border-slate-400 transition-all duration-200 text-text placeholder:text-muted focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary resize-none"
        />
      </div>

      {/* Address Line 1 */}
      <Input
        label="Address Line 1"
        name="address_line_1"
        value={formData.address_line_1}
        onChange={handleChange}
        placeholder="e.g., 123 Main Street"
        error={errors.address_line_1}
        required
      />

      {/* Address Line 2 */}
      <Input
        label="Address Line 2"
        name="address_line_2"
        value={formData.address_line_2}
        onChange={handleChange}
        placeholder="e.g., Building A, Floor 2 (Optional)"
      />

      {/* City & State/County */}
      <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <Input
          label="City"
          name="city"
          value={formData.city}
          onChange={handleChange}
          placeholder="e.g., Nairobi"
          error={errors.city}
          required
        />
        <Input
          label="State/County"
          name="state"
          value={formData.state}
          onChange={handleChange}
          placeholder="e.g., Nairobi"
          error={errors.state}
          required
        />
      </div>

      {/* Postal Code & Country */}
      <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <Input
          label="Postal Code"
          name="postal_code"
          value={formData.postal_code}
          onChange={handleChange}
          placeholder="e.g., 00100 (Optional)"
        />
        <Input
          label="Country"
          name="country"
          value={formData.country}
          onChange={handleChange}
          placeholder="e.g., Kenya"
          error={errors.country}
          required
        />
      </div>

      {/* Total Units */}
      <Input
        label="Total Units"
        name="total_units"
        type="number"
        min="1"
        value={formData.total_units}
        onChange={handleChange}
        placeholder="e.g., 10"
        error={errors.total_units}
        required
      />

      {/* Commission */}
      <div className="p-4 bg-slate-50 rounded-lg border border-slate-200">
        <h3 className="font-medium text-text mb-4">Commission Structure</h3>
        <Input
          label="Commission Percentage (%)"
          name="commission_percentage"
          type="number"
          min="0"
          max="100"
          step="0.01"
          value={formData.commission_percentage}
          onChange={handleChange}
          placeholder="e.g., 10"
          error={errors.commission_percentage}
          required
        />
        <p className="text-xs text-muted mt-2">
          This percentage will be deducted from each rent payment as platform commission.
        </p>
      </div>

      {/* Submit Button */}
      <div className="flex justify-end gap-3 pt-4">
        <Button type="submit" loading={isLoading} disabled={isLoading}>
          {submitLabel}
        </Button>
      </div>
    </form>
  );
};

export default PropertyForm;
