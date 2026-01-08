import React, { useState, useEffect } from 'react';
import { useNavigate, useParams, Link } from 'react-router-dom';
import { toast } from 'react-toastify';
import tenantService from '../../services/tenantService';

/**
 * TenantForm - Create/Edit tenant form
 */
const TenantForm = () => {
    const navigate = useNavigate();
    const { id } = useParams();
    const isEditing = Boolean(id);

    const [loading, setLoading] = useState(false);
    const [submitting, setSubmitting] = useState(false);
    const [createdCredentials, setCreatedCredentials] = useState(null);
    const [formData, setFormData] = useState({
        name: '',
        email: '',
        phone: '',
        id_number: '',
        emergency_contact_name: '',
        emergency_contact_phone: '',
        occupation: '',
        employer: '',
        notes: '',
    });
    const [errors, setErrors] = useState({});

    useEffect(() => {
        if (isEditing) {
            fetchTenant();
        }
    }, [id]);

    const fetchTenant = async () => {
        setLoading(true);
        try {
            const response = await tenantService.getTenant(id);
            const tenant = response.data;
            setFormData({
                name: tenant.name || '',
                email: tenant.email || '',
                phone: tenant.phone || '',
                id_number: tenant.id_number || '',
                emergency_contact_name: tenant.emergency_contact_name || '',
                emergency_contact_phone: tenant.emergency_contact_phone || '',
                occupation: tenant.occupation || '',
                employer: tenant.employer || '',
                notes: tenant.notes || '',
            });
        } catch (err) {
            toast.error('Failed to load tenant');
            navigate('/company/tenants');
        } finally {
            setLoading(false);
        }
    };

    const handleChange = (e) => {
        const { name, value } = e.target;
        setFormData({ ...formData, [name]: value });
        // Clear error when field changes
        if (errors[name]) {
            setErrors({ ...errors, [name]: null });
        }
    };

    const validate = () => {
        const newErrors = {};
        if (!formData.name.trim()) newErrors.name = 'Name is required';
        if (!formData.email.trim()) {
            newErrors.email = 'Email is required';
        } else if (!/\S+@\S+\.\S+/.test(formData.email)) {
            newErrors.email = 'Please enter a valid email';
        }
        if (!formData.phone.trim()) newErrors.phone = 'Phone is required';
        setErrors(newErrors);
        return Object.keys(newErrors).length === 0;
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        if (!validate()) return;

        setSubmitting(true);
        try {
            if (isEditing) {
                await tenantService.updateTenant(id, formData);
                toast.success('Tenant updated successfully');
                navigate('/company/tenants');
            } else {
                const response = await tenantService.createTenant(formData);
                // Show credentials for new tenant
                if (response.data?.credentials) {
                    setCreatedCredentials(response.data.credentials);
                } else {
                    toast.success('Tenant created successfully');
                    navigate('/company/tenants');
                }
            }
        } catch (err) {
            if (err.errors) {
                setErrors(err.errors);
            } else {
                toast.error(err.message || 'Failed to save tenant');
            }
        } finally {
            setSubmitting(false);
        }
    };

    if (loading) {
        return (
            <div className="flex justify-center py-12">
                <div className="w-8 h-8 border-4 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
            </div>
        );
    }

    // Show credentials after creation
    if (createdCredentials) {
        return (
            <div className="max-w-lg mx-auto">
                <div className="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 text-center">
                    <div className="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg className="w-8 h-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <h2 className="text-2xl font-bold text-gray-900 mb-2">Tenant Created!</h2>
                    <p className="text-gray-600 mb-6">
                        The tenant account has been created successfully. Here are the login credentials:
                    </p>
                    <div className="bg-blue-50 rounded-xl p-5 text-left mb-6">
                        <div className="space-y-3">
                            <div>
                                <p className="text-sm text-gray-500 mb-1">Email</p>
                                <p className="font-mono text-gray-900 bg-white px-3 py-2 rounded-lg border border-blue-100">
                                    {createdCredentials.email}
                                </p>
                            </div>
                            <div>
                                <p className="text-sm text-gray-500 mb-1">Temporary Password</p>
                                <p className="font-mono text-gray-900 bg-white px-3 py-2 rounded-lg border border-blue-100">
                                    {createdCredentials.temporary_password}
                                </p>
                            </div>
                        </div>
                        <p className="text-xs text-blue-600 mt-4 flex items-center gap-1">
                            <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            These credentials have been sent to the tenant via email.
                        </p>
                    </div>
                    <div className="flex gap-3">
                        <Link
                            to="/company/tenants"
                            className="flex-1 px-4 py-2.5 text-gray-700 bg-gray-100 font-medium rounded-lg hover:bg-gray-200 transition-colors"
                        >
                            Back to Tenants
                        </Link>
                        <Link
                            to="/company/leases/create"
                            className="flex-1 px-4 py-2.5 text-white bg-blue-600 font-medium rounded-lg hover:bg-blue-700 transition-colors"
                        >
                            Create Lease
                        </Link>
                    </div>
                </div>
            </div>
        );
    }

    return (
        <div className="max-w-2xl mx-auto">
            {/* Header */}
            <div className="mb-6">
                <Link
                    to="/company/tenants"
                    className="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 mb-4"
                >
                    <svg className="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
                    </svg>
                    Back to Tenants
                </Link>
                <h1 className="text-2xl font-bold text-gray-900">
                    {isEditing ? 'Edit Tenant' : 'Add New Tenant'}
                </h1>
                <p className="text-gray-500 mt-1">
                    {isEditing
                        ? 'Update tenant information'
                        : 'Enter tenant details. A welcome email with login credentials will be sent automatically.'}
                </p>
            </div>

            {/* Form */}
            <form onSubmit={handleSubmit} className="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div className="space-y-6">
                    {/* Basic Info */}
                    <div>
                        <h3 className="text-sm font-medium text-gray-900 mb-4 flex items-center gap-2">
                            <span className="w-6 h-6 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-xs font-bold">
                                1
                            </span>
                            Basic Information
                        </h3>
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div className="md:col-span-2">
                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                    Full Name <span className="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    name="name"
                                    value={formData.name}
                                    onChange={handleChange}
                                    className={`w-full px-4 py-2.5 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 ${errors.name ? 'border-red-300' : 'border-gray-200'
                                        }`}
                                    placeholder="John Doe"
                                />
                                {errors.name && <p className="text-red-500 text-sm mt-1">{errors.name}</p>}
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                    Email <span className="text-red-500">*</span>
                                </label>
                                <input
                                    type="email"
                                    name="email"
                                    value={formData.email}
                                    onChange={handleChange}
                                    disabled={isEditing}
                                    className={`w-full px-4 py-2.5 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 ${errors.email ? 'border-red-300' : 'border-gray-200'
                                        } ${isEditing ? 'bg-gray-50' : ''}`}
                                    placeholder="john@example.com"
                                />
                                {errors.email && <p className="text-red-500 text-sm mt-1">{errors.email}</p>}
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                    Phone <span className="text-red-500">*</span>
                                </label>
                                <input
                                    type="tel"
                                    name="phone"
                                    value={formData.phone}
                                    onChange={handleChange}
                                    className={`w-full px-4 py-2.5 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 ${errors.phone ? 'border-red-300' : 'border-gray-200'
                                        }`}
                                    placeholder="+254 7XX XXX XXX"
                                />
                                {errors.phone && <p className="text-red-500 text-sm mt-1">{errors.phone}</p>}
                            </div>
                            <div className="md:col-span-2">
                                <label className="block text-sm font-medium text-gray-700 mb-1">ID Number</label>
                                <input
                                    type="text"
                                    name="id_number"
                                    value={formData.id_number}
                                    onChange={handleChange}
                                    className="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="National ID or Passport"
                                />
                            </div>
                        </div>
                    </div>

                    <hr className="border-gray-100" />

                    {/* Emergency Contact */}
                    <div>
                        <h3 className="text-sm font-medium text-gray-900 mb-4 flex items-center gap-2">
                            <span className="w-6 h-6 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-xs font-bold">
                                2
                            </span>
                            Emergency Contact
                        </h3>
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Contact Name</label>
                                <input
                                    type="text"
                                    name="emergency_contact_name"
                                    value={formData.emergency_contact_name}
                                    onChange={handleChange}
                                    className="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Jane Doe"
                                />
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Contact Phone</label>
                                <input
                                    type="tel"
                                    name="emergency_contact_phone"
                                    value={formData.emergency_contact_phone}
                                    onChange={handleChange}
                                    className="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="+254 7XX XXX XXX"
                                />
                            </div>
                        </div>
                    </div>

                    <hr className="border-gray-100" />

                    {/* Employment */}
                    <div>
                        <h3 className="text-sm font-medium text-gray-900 mb-4 flex items-center gap-2">
                            <span className="w-6 h-6 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-xs font-bold">
                                3
                            </span>
                            Employment Information
                        </h3>
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Occupation</label>
                                <input
                                    type="text"
                                    name="occupation"
                                    value={formData.occupation}
                                    onChange={handleChange}
                                    className="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Software Engineer"
                                />
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Employer</label>
                                <input
                                    type="text"
                                    name="employer"
                                    value={formData.employer}
                                    onChange={handleChange}
                                    className="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Tech Corp Ltd"
                                />
                            </div>
                        </div>
                    </div>

                    <hr className="border-gray-100" />

                    {/* Notes */}
                    <div>
                        <h3 className="text-sm font-medium text-gray-900 mb-4 flex items-center gap-2">
                            <span className="w-6 h-6 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-xs font-bold">
                                4
                            </span>
                            Additional Notes
                        </h3>
                        <textarea
                            name="notes"
                            value={formData.notes}
                            onChange={handleChange}
                            rows={3}
                            className="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Any additional information about this tenant..."
                        />
                    </div>
                </div>

                {/* Actions */}
                <div className="flex gap-3 mt-8 pt-6 border-t border-gray-100">
                    <Link
                        to="/company/tenants"
                        className="flex-1 px-4 py-2.5 text-gray-700 bg-gray-100 font-medium rounded-lg hover:bg-gray-200 transition-colors text-center"
                    >
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        disabled={submitting}
                        className="flex-1 px-4 py-2.5 text-white bg-blue-600 font-medium rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors flex items-center justify-center gap-2"
                    >
                        {submitting ? (
                            <>
                                <div className="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                                <span>Saving...</span>
                            </>
                        ) : (
                            <span>{isEditing ? 'Update Tenant' : 'Create Tenant'}</span>
                        )}
                    </button>
                </div>
            </form>
        </div>
    );
};

export default TenantForm;
