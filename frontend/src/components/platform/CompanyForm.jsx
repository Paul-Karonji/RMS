import React, { useState } from 'react';

const CompanyForm = ({ onSubmit, loading = false, initialData = null }) => {
  const [formData, setFormData] = useState({
    company_name: initialData?.company_name || '',
    admin_email: initialData?.admin_email || '',
    admin_phone: initialData?.admin_phone || '',
    admin_name: initialData?.admin_name || '',
    company_address: initialData?.company_address || '',
    pricing_model: initialData?.pricing_model || 'payment_processing',
    
    cashout_fee_percentage: initialData?.cashout_fee_percentage || 3.00,
    min_platform_fee_percentage: initialData?.min_platform_fee_percentage || 5.00,
    max_platform_fee_percentage: initialData?.max_platform_fee_percentage || 15.00,
    default_platform_fee_percentage: initialData?.default_platform_fee_percentage || 10.00,
    
    subscription_plan: initialData?.subscription_plan || 'monthly',
  });

  const [errors, setErrors] = useState({});

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({ ...prev, [name]: value }));
    if (errors[name]) {
      setErrors(prev => ({ ...prev, [name]: null }));
    }
  };

  const validate = () => {
    const newErrors = {};

    if (!formData.company_name) newErrors.company_name = 'Company name is required';
    if (!formData.admin_email) newErrors.admin_email = 'Admin email is required';
    if (!formData.admin_phone) newErrors.admin_phone = 'Admin phone is required';

    if (formData.pricing_model === 'payment_processing') {
      if (parseFloat(formData.max_platform_fee_percentage) < parseFloat(formData.min_platform_fee_percentage)) {
        newErrors.max_platform_fee_percentage = 'Max fee must be >= min fee';
      }
      const defaultFee = parseFloat(formData.default_platform_fee_percentage);
      const minFee = parseFloat(formData.min_platform_fee_percentage);
      const maxFee = parseFloat(formData.max_platform_fee_percentage);
      
      if (defaultFee < minFee || defaultFee > maxFee) {
        newErrors.default_platform_fee_percentage = 'Default fee must be within min-max range';
      }
    }

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    if (validate()) {
      onSubmit(formData);
    }
  };

  return (
    <form onSubmit={handleSubmit} className="space-y-6 max-w-2xl">
      <div className="bg-white rounded-lg shadow p-6">
        <h3 className="text-lg font-semibold mb-4">Company Information</h3>
        
        <div className="space-y-4">
          <div>
            <label className="block text-sm font-medium text-gray-700">Company Name *</label>
            <input
              type="text"
              name="company_name"
              value={formData.company_name}
              onChange={handleChange}
              className={`mt-1 block w-full rounded-md border ${
                errors.company_name ? 'border-red-500' : 'border-gray-300'
              } px-3 py-2 focus:border-blue-500 focus:ring-blue-500`}
            />
            {errors.company_name && (
              <p className="mt-1 text-sm text-red-600">{errors.company_name}</p>
            )}
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700">Admin Email *</label>
            <input
              type="email"
              name="admin_email"
              value={formData.admin_email}
              onChange={handleChange}
              className={`mt-1 block w-full rounded-md border ${
                errors.admin_email ? 'border-red-500' : 'border-gray-300'
              } px-3 py-2`}
            />
            {errors.admin_email && (
              <p className="mt-1 text-sm text-red-600">{errors.admin_email}</p>
            )}
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700">Admin Phone *</label>
            <input
              type="tel"
              name="admin_phone"
              value={formData.admin_phone}
              onChange={handleChange}
              placeholder="+254712345678"
              className={`mt-1 block w-full rounded-md border ${
                errors.admin_phone ? 'border-red-500' : 'border-gray-300'
              } px-3 py-2`}
            />
            {errors.admin_phone && (
              <p className="mt-1 text-sm text-red-600">{errors.admin_phone}</p>
            )}
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700">Admin Name</label>
            <input
              type="text"
              name="admin_name"
              value={formData.admin_name}
              onChange={handleChange}
              className="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2"
            />
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700">Company Address</label>
            <textarea
              name="company_address"
              value={formData.company_address}
              onChange={handleChange}
              rows="3"
              className="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2"
            />
          </div>
        </div>
      </div>

      <div className="bg-white rounded-lg shadow p-6">
        <h3 className="text-lg font-semibold mb-4">Pricing Model</h3>
        
        <div className="space-y-4">
          <div>
            <label className="block text-sm font-medium text-gray-700">Pricing Model *</label>
            <select
              name="pricing_model"
              value={formData.pricing_model}
              onChange={handleChange}
              className="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2"
            >
              <option value="payment_processing">Payment Processing</option>
              <option value="listings_only">Listings Only</option>
            </select>
          </div>

          {formData.pricing_model === 'payment_processing' && (
            <>
              <div>
                <label className="block text-sm font-medium text-gray-700">
                  Cashout Fee Percentage (%)
                </label>
                <input
                  type="number"
                  name="cashout_fee_percentage"
                  value={formData.cashout_fee_percentage}
                  onChange={handleChange}
                  step="0.01"
                  min="0"
                  max="10"
                  className="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2"
                />
              </div>

              <div className="grid grid-cols-3 gap-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700">Min Fee (%)</label>
                  <input
                    type="number"
                    name="min_platform_fee_percentage"
                    value={formData.min_platform_fee_percentage}
                    onChange={handleChange}
                    step="0.01"
                    min="0"
                    className="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2"
                  />
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700">Default Fee (%)</label>
                  <input
                    type="number"
                    name="default_platform_fee_percentage"
                    value={formData.default_platform_fee_percentage}
                    onChange={handleChange}
                    step="0.01"
                    className={`mt-1 block w-full rounded-md border ${
                      errors.default_platform_fee_percentage ? 'border-red-500' : 'border-gray-300'
                    } px-3 py-2`}
                  />
                  {errors.default_platform_fee_percentage && (
                    <p className="mt-1 text-sm text-red-600">{errors.default_platform_fee_percentage}</p>
                  )}
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700">Max Fee (%)</label>
                  <input
                    type="number"
                    name="max_platform_fee_percentage"
                    value={formData.max_platform_fee_percentage}
                    onChange={handleChange}
                    step="0.01"
                    className={`mt-1 block w-full rounded-md border ${
                      errors.max_platform_fee_percentage ? 'border-red-500' : 'border-gray-300'
                    } px-3 py-2`}
                  />
                  {errors.max_platform_fee_percentage && (
                    <p className="mt-1 text-sm text-red-600">{errors.max_platform_fee_percentage}</p>
                  )}
                </div>
              </div>
            </>
          )}

          {formData.pricing_model === 'listings_only' && (
            <div>
              <label className="block text-sm font-medium text-gray-700">Subscription Plan</label>
              <select
                name="subscription_plan"
                value={formData.subscription_plan}
                onChange={handleChange}
                className="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2"
              >
                <option value="weekly">Weekly (KES 500)</option>
                <option value="monthly">Monthly (KES 1,500)</option>
                <option value="annual">Annual (KES 15,000)</option>
              </select>
            </div>
          )}
        </div>
      </div>

      <div className="flex justify-end space-x-4">
        <button
          type="button"
          onClick={() => window.history.back()}
          className="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
        >
          Cancel
        </button>
        <button
          type="submit"
          disabled={loading}
          className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
        >
          {loading ? 'Creating...' : 'Create Company'}
        </button>
      </div>
    </form>
  );
};

export default CompanyForm;
