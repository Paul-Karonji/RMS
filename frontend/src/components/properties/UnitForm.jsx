import { useState, useEffect } from 'react';
import { Button, Input, Alert } from '../common';

const UNIT_TYPES = [
  { value: 'Studio', label: 'Studio' },
  { value: '1BR', label: '1 Bedroom' },
  { value: '2BR', label: '2 Bedroom' },
  { value: '3BR', label: '3 Bedroom' },
  { value: '4BR', label: '4 Bedroom' },
  { value: '5BR+', label: '5+ Bedroom' },
  { value: 'Bedsitter', label: 'Bedsitter' },
  { value: 'Single Room', label: 'Single Room' },
  { value: 'Office Suite', label: 'Office Suite' },
  { value: 'Shop', label: 'Shop' },
  { value: 'Warehouse', label: 'Warehouse' },
];

const UnitForm = ({
  initialData = {},
  onSubmit,
  isLoading = false,
  error = null,
  submitLabel = 'Submit',
  isEdit = false,
  isOccupied = false,
}) => {
  const [formData, setFormData] = useState({
    unit_number: '',
    unit_type: '1BR',
    bedrooms: '',
    bathrooms: '',
    square_feet: '',
    floor_number: '',
    rent_amount: '',
    deposit_amount: '',
    description: '',
    is_featured: false,
  });

  const [errors, setErrors] = useState({});

  useEffect(() => {
    if (initialData && Object.keys(initialData).length > 0) {
      setFormData({
        unit_number: initialData.unit_number || '',
        unit_type: initialData.unit_type || '1BR',
        bedrooms: initialData.bedrooms?.toString() || '',
        bathrooms: initialData.bathrooms?.toString() || '',
        square_feet: initialData.square_feet?.toString() || '',
        floor_number: initialData.floor_number?.toString() || '',
        rent_amount: initialData.rent_amount?.toString() || '',
        deposit_amount: initialData.deposit_amount?.toString() || '',
        description: initialData.description || '',
        is_featured: initialData.is_featured || false,
      });
    }
  }, [initialData]);

  const handleChange = (e) => {
    const { name, value, type, checked } = e.target;
    setFormData((prev) => ({
      ...prev,
      [name]: type === 'checkbox' ? checked : value,
    }));
    if (errors[name]) {
      setErrors((prev) => ({ ...prev, [name]: null }));
    }
  };

  const validate = () => {
    const newErrors = {};

    if (!formData.unit_number.trim()) {
      newErrors.unit_number = 'Unit number is required';
    }

    if (!formData.unit_type) {
      newErrors.unit_type = 'Unit type is required';
    }

    if (!formData.bedrooms && formData.bedrooms !== 0) {
      newErrors.bedrooms = 'Number of bedrooms is required';
    } else if (parseInt(formData.bedrooms) < 0) {
      newErrors.bedrooms = 'Bedrooms cannot be negative';
    }

    if (!formData.bathrooms) {
      newErrors.bathrooms = 'Number of bathrooms is required';
    } else if (parseInt(formData.bathrooms) < 1) {
      newErrors.bathrooms = 'At least 1 bathroom is required';
    }

    if (!formData.rent_amount) {
      newErrors.rent_amount = 'Rent amount is required';
    } else if (parseFloat(formData.rent_amount) <= 0) {
      newErrors.rent_amount = 'Rent amount must be greater than 0';
    }

    if (!formData.deposit_amount) {
      newErrors.deposit_amount = 'Deposit amount is required';
    } else if (parseFloat(formData.deposit_amount) < 0) {
      newErrors.deposit_amount = 'Deposit cannot be negative';
    }

    if (formData.square_feet && parseFloat(formData.square_feet) < 0) {
      newErrors.square_feet = 'Square feet cannot be negative';
    }

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = (e) => {
    e.preventDefault();

    if (!validate()) return;

    const submitData = {
      unit_number: formData.unit_number,
      unit_type: formData.unit_type,
      bedrooms: parseInt(formData.bedrooms) || 0,
      bathrooms: parseInt(formData.bathrooms) || 1,
      rent_amount: parseFloat(formData.rent_amount),
      deposit_amount: parseFloat(formData.deposit_amount),
      description: formData.description || null,
      is_featured: formData.is_featured,
    };

    if (formData.square_feet) {
      submitData.square_feet = parseInt(formData.square_feet);
    }
    if (formData.floor_number) {
      submitData.floor_number = parseInt(formData.floor_number);
    }

    onSubmit(submitData);
  };

  return (
    <form onSubmit={handleSubmit} className="space-y-6">
      {error && <Alert type="error" message={error} />}

      {isOccupied && (
        <Alert
          type="warning"
          message="This unit is currently occupied. Only description and featured status can be modified."
        />
      )}

      {/* Unit Number & Type */}
      <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <Input
          label="Unit Number"
          name="unit_number"
          value={formData.unit_number}
          onChange={handleChange}
          placeholder="e.g., A101"
          error={errors.unit_number}
          disabled={isOccupied}
          required
        />

        <div className="w-full">
          <label className="block text-sm font-medium text-text mb-1.5">
            Unit Type <span className="text-red-500">*</span>
          </label>
          <select
            name="unit_type"
            value={formData.unit_type}
            onChange={handleChange}
            disabled={isOccupied}
            className={`
              w-full px-4 py-3 rounded-lg border transition-all duration-200
              text-text bg-white
              focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary
              disabled:bg-slate-100 disabled:cursor-not-allowed
              ${errors.unit_type
                ? 'border-error focus:ring-error focus:border-error'
                : 'border-slate-300 hover:border-slate-400'
              }
            `}
          >
            {UNIT_TYPES.map((type) => (
              <option key={type.value} value={type.value}>
                {type.label}
              </option>
            ))}
          </select>
          {errors.unit_type && (
            <p className="mt-1.5 text-sm text-error">{errors.unit_type}</p>
          )}
        </div>
      </div>

      {/* Bedrooms & Bathrooms */}
      <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <Input
          label="Bedrooms"
          name="bedrooms"
          type="number"
          min="0"
          value={formData.bedrooms}
          onChange={handleChange}
          placeholder="e.g., 2"
          error={errors.bedrooms}
          disabled={isOccupied}
          required
        />
        <Input
          label="Bathrooms"
          name="bathrooms"
          type="number"
          min="1"
          value={formData.bathrooms}
          onChange={handleChange}
          placeholder="e.g., 1"
          error={errors.bathrooms}
          disabled={isOccupied}
          required
        />
      </div>

      {/* Square Feet & Floor */}
      <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <Input
          label="Square Feet"
          name="square_feet"
          type="number"
          min="0"
          value={formData.square_feet}
          onChange={handleChange}
          placeholder="e.g., 850"
          error={errors.square_feet}
          disabled={isOccupied}
          hint="Optional"
        />
        <Input
          label="Floor Number"
          name="floor_number"
          type="number"
          value={formData.floor_number}
          onChange={handleChange}
          placeholder="e.g., 1"
          disabled={isOccupied}
          hint="Optional"
        />
      </div>

      {/* Pricing */}
      <div className="p-4 bg-slate-50 rounded-lg border border-slate-200">
        <h3 className="font-medium text-text mb-4">Pricing</h3>
        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <Input
            label="Monthly Rent (KES)"
            name="rent_amount"
            type="number"
            min="0"
            step="100"
            value={formData.rent_amount}
            onChange={handleChange}
            placeholder="e.g., 50000"
            error={errors.rent_amount}
            disabled={isOccupied}
            required
          />
          <Input
            label="Deposit Amount (KES)"
            name="deposit_amount"
            type="number"
            min="0"
            step="100"
            value={formData.deposit_amount}
            onChange={handleChange}
            placeholder="e.g., 50000"
            error={errors.deposit_amount}
            disabled={isOccupied}
            required
          />
        </div>
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
          placeholder="Brief description of the unit..."
          className="w-full px-4 py-3 rounded-lg border border-slate-300 hover:border-slate-400 transition-all duration-200 text-text placeholder:text-muted focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary resize-none"
        />
      </div>

      {/* Featured */}
      <div className="flex items-center gap-3">
        <input
          type="checkbox"
          id="is_featured"
          name="is_featured"
          checked={formData.is_featured}
          onChange={handleChange}
          className="w-4 h-4 text-primary border-slate-300 rounded focus:ring-primary"
        />
        <label htmlFor="is_featured" className="text-sm text-text">
          Mark as featured unit (will be highlighted in listings)
        </label>
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

export default UnitForm;
