import React, { useState, useEffect } from 'react';
import { useNavigate, useParams, useSearchParams, Link } from 'react-router-dom';
import { toast } from 'react-toastify';
import tenantService from '../../services/tenantService';
import propertyService from '../../services/propertyService';
import ProRatedCalculation from '../../components/tenants/ProRatedCalculation';

/**
 * LeaseForm - Multi-step lease creation wizard
 */
const LeaseForm = () => {
    const navigate = useNavigate();
    const { id } = useParams();
    const [searchParams] = useSearchParams();
    const isEditing = Boolean(id);
    const preselectedTenantId = searchParams.get('tenant_id');
    const isRenewal = searchParams.get('renew') === 'true';

    // Wizard state
    const [currentStep, setCurrentStep] = useState(1);
    const [loading, setLoading] = useState(false);
    const [submitting, setSubmitting] = useState(false);

    // Data lists
    const [tenants, setTenants] = useState([]);
    const [units, setUnits] = useState([]);
    const [loadingTenants, setLoadingTenants] = useState(false);
    const [loadingUnits, setLoadingUnits] = useState(false);

    // Form data
    const [formData, setFormData] = useState({
        tenant_id: preselectedTenantId || '',
        unit_id: '',
        start_date: '',
        end_date: '',
        monthly_rent: '',
        deposit_amount: '',
        payment_due_day: 1,
        payment_type: 'manual', // 'manual' or 'recurring'
        notes: '',
    });

    // Selected items for display
    const [selectedTenant, setSelectedTenant] = useState(null);
    const [selectedUnit, setSelectedUnit] = useState(null);

    const [errors, setErrors] = useState({});

    useEffect(() => {
        fetchTenants();
        fetchUnits();
        if (isEditing) {
            fetchLease();
        }
    }, [id]);

    useEffect(() => {
        if (preselectedTenantId && tenants.length > 0) {
            const tenant = tenants.find((t) => t.id === preselectedTenantId);
            if (tenant) {
                setSelectedTenant(tenant);
                setFormData({ ...formData, tenant_id: preselectedTenantId });
            }
        }
    }, [preselectedTenantId, tenants]);

    const fetchTenants = async () => {
        setLoadingTenants(true);
        try {
            const response = await tenantService.getTenants({ per_page: 100 });
            setTenants(response.data || []);
        } catch (err) {
            toast.error('Failed to load tenants');
        } finally {
            setLoadingTenants(false);
        }
    };

    const fetchUnits = async () => {
        setLoadingUnits(true);
        try {
            const response = await propertyService.getUnits({ status: 'available', per_page: 100 });
            setUnits(response.data || []);
        } catch (err) {
            toast.error('Failed to load units');
        } finally {
            setLoadingUnits(false);
        }
    };

    const fetchLease = async () => {
        setLoading(true);
        try {
            const response = await tenantService.getLease(id);
            const lease = response.data;
            setFormData({
                tenant_id: lease.tenant_id || '',
                unit_id: lease.unit_id || '',
                start_date: lease.start_date || '',
                end_date: lease.end_date || '',
                monthly_rent: lease.monthly_rent || '',
                deposit_amount: lease.deposit_amount || '',
                payment_due_day: lease.payment_due_day || 1,
                payment_type: lease.payment_type || 'manual',
                notes: lease.notes || '',
            });
            setSelectedTenant(lease.tenant);
            setSelectedUnit(lease.unit);
            // Skip to review step for editing
            setCurrentStep(4);
        } catch (err) {
            toast.error('Failed to load lease');
            navigate('/company/leases');
        } finally {
            setLoading(false);
        }
    };

    const handleChange = (e) => {
        const { name, value } = e.target;
        setFormData({ ...formData, [name]: value });
        if (errors[name]) {
            setErrors({ ...errors, [name]: null });
        }
    };

    const selectTenant = (tenant) => {
        setSelectedTenant(tenant);
        setFormData({ ...formData, tenant_id: tenant.id });
    };

    const selectUnit = (unit) => {
        setSelectedUnit(unit);
        setFormData({
            ...formData,
            unit_id: unit.id,
            monthly_rent: unit.monthly_rent || '',
            deposit_amount: unit.deposit_amount || unit.monthly_rent || '',
        });
    };

    const validateStep = (step) => {
        const newErrors = {};

        if (step === 1) {
            if (!formData.tenant_id) newErrors.tenant_id = 'Please select a tenant';
        }

        if (step === 2) {
            if (!formData.unit_id) newErrors.unit_id = 'Please select a unit';
        }

        if (step === 3) {
            if (!formData.start_date) newErrors.start_date = 'Start date is required';
            if (!formData.end_date) newErrors.end_date = 'End date is required';
            if (!formData.monthly_rent) newErrors.monthly_rent = 'Monthly rent is required';
            if (formData.start_date && formData.end_date && new Date(formData.end_date) <= new Date(formData.start_date)) {
                newErrors.end_date = 'End date must be after start date';
            }
        }

        setErrors(newErrors);
        return Object.keys(newErrors).length === 0;
    };

    const nextStep = () => {
        if (validateStep(currentStep)) {
            setCurrentStep(currentStep + 1);
        }
    };

    const prevStep = () => {
        setCurrentStep(currentStep - 1);
    };

    const handleSubmit = async () => {
        setSubmitting(true);
        try {
            if (isEditing) {
                await tenantService.updateLease(id, formData);
                toast.success('Lease updated successfully');
            } else {
                await tenantService.createLease(formData);
                toast.success('Lease created successfully');
            }
            navigate('/company/leases');
        } catch (err) {
            if (err.errors) {
                setErrors(err.errors);
                // Go back to relevant step
                if (err.errors.tenant_id) setCurrentStep(1);
                else if (err.errors.unit_id) setCurrentStep(2);
                else if (err.errors.start_date || err.errors.end_date || err.errors.monthly_rent) setCurrentStep(3);
            } else {
                toast.error(err.message || 'Failed to save lease');
            }
        } finally {
            setSubmitting(false);
        }
    };

    const formatDate = (dateString) => {
        if (!dateString) return '-';
        return new Date(dateString).toLocaleDateString('en-US', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric',
        });
    };

    if (loading) {
        return (
            <div className="flex justify-center py-12">
                <div className="w-8 h-8 border-4 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
            </div>
        );
    }

    const steps = [
        { number: 1, title: 'Select Tenant' },
        { number: 2, title: 'Select Unit' },
        { number: 3, title: 'Lease Terms' },
        { number: 4, title: 'Review' },
    ];

    return (
        <div className="max-w-4xl mx-auto">
            {/* Header */}
            <div className="mb-6">
                <Link
                    to="/company/leases"
                    className="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 mb-4"
                >
                    <svg className="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
                    </svg>
                    Back to Leases
                </Link>
                <h1 className="text-2xl font-bold text-gray-900">
                    {isRenewal ? 'Renew Lease' : isEditing ? 'Edit Lease' : 'Create New Lease'}
                </h1>
                <p className="text-gray-500 mt-1">
                    {isEditing
                        ? 'Update lease details'
                        : 'Set up a new lease agreement step by step'}
                </p>
            </div>

            {/* Progress Steps */}
            <div className="mb-8">
                <div className="flex items-center justify-between">
                    {steps.map((step, index) => (
                        <React.Fragment key={step.number}>
                            <div className="flex flex-col items-center">
                                <div
                                    className={`w-10 h-10 rounded-full flex items-center justify-center font-semibold text-sm ${currentStep >= step.number
                                            ? 'bg-blue-600 text-white'
                                            : 'bg-gray-200 text-gray-500'
                                        }`}
                                >
                                    {currentStep > step.number ? (
                                        <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                                        </svg>
                                    ) : (
                                        step.number
                                    )}
                                </div>
                                <span
                                    className={`text-xs mt-2 ${currentStep >= step.number ? 'text-blue-600 font-medium' : 'text-gray-500'
                                        }`}
                                >
                                    {step.title}
                                </span>
                            </div>
                            {index < steps.length - 1 && (
                                <div
                                    className={`flex-1 h-1 mx-2 rounded ${currentStep > step.number ? 'bg-blue-600' : 'bg-gray-200'
                                        }`}
                                ></div>
                            )}
                        </React.Fragment>
                    ))}
                </div>
            </div>

            {/* Form Content */}
            <div className="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                {/* Step 1: Select Tenant */}
                {currentStep === 1 && (
                    <div>
                        <h2 className="text-lg font-semibold text-gray-900 mb-4">Select Tenant</h2>
                        {errors.tenant_id && (
                            <p className="text-red-500 text-sm mb-4">{errors.tenant_id}</p>
                        )}

                        {/* Search */}
                        <div className="relative mb-4">
                            <svg
                                className="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth={2}
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                />
                            </svg>
                            <input
                                type="text"
                                placeholder="Search tenants..."
                                className="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            />
                        </div>

                        {/* Tenant List */}
                        {loadingTenants ? (
                            <div className="flex justify-center py-8">
                                <div className="w-6 h-6 border-2 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
                            </div>
                        ) : tenants.length === 0 ? (
                            <div className="text-center py-8">
                                <p className="text-gray-500 mb-4">No tenants found</p>
                                <Link
                                    to="/company/tenants/create"
                                    className="text-blue-600 hover:text-blue-700 font-medium"
                                >
                                    Create a tenant first
                                </Link>
                            </div>
                        ) : (
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-80 overflow-y-auto">
                                {tenants.filter((t) => t.is_active).map((tenant) => (
                                    <button
                                        key={tenant.id}
                                        type="button"
                                        onClick={() => selectTenant(tenant)}
                                        className={`p-4 rounded-lg border-2 text-left transition-all ${selectedTenant?.id === tenant.id
                                                ? 'border-blue-500 bg-blue-50'
                                                : 'border-gray-200 hover:border-gray-300'
                                            }`}
                                    >
                                        <div className="flex items-center gap-3">
                                            <div className="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 font-semibold">
                                                {tenant.name?.charAt(0).toUpperCase()}
                                            </div>
                                            <div>
                                                <p className="font-medium text-gray-900">{tenant.name}</p>
                                                <p className="text-sm text-gray-500">{tenant.email}</p>
                                            </div>
                                        </div>
                                    </button>
                                ))}
                            </div>
                        )}
                    </div>
                )}

                {/* Step 2: Select Unit */}
                {currentStep === 2 && (
                    <div>
                        <h2 className="text-lg font-semibold text-gray-900 mb-4">Select Unit</h2>
                        {errors.unit_id && (
                            <p className="text-red-500 text-sm mb-4">{errors.unit_id}</p>
                        )}

                        {/* Unit List */}
                        {loadingUnits ? (
                            <div className="flex justify-center py-8">
                                <div className="w-6 h-6 border-2 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
                            </div>
                        ) : units.length === 0 ? (
                            <div className="text-center py-8">
                                <p className="text-gray-500">No available units found</p>
                            </div>
                        ) : (
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-80 overflow-y-auto">
                                {units.map((unit) => (
                                    <button
                                        key={unit.id}
                                        type="button"
                                        onClick={() => selectUnit(unit)}
                                        className={`p-4 rounded-lg border-2 text-left transition-all ${selectedUnit?.id === unit.id
                                                ? 'border-blue-500 bg-blue-50'
                                                : 'border-gray-200 hover:border-gray-300'
                                            }`}
                                    >
                                        <div className="flex justify-between items-start">
                                            <div>
                                                <p className="font-medium text-gray-900">Unit {unit.unit_number}</p>
                                                <p className="text-sm text-gray-500">{unit.property?.property_name}</p>
                                                <p className="text-xs text-gray-400 mt-1">
                                                    {unit.bedrooms} bed • {unit.bathrooms} bath • {unit.size_sqft} sqft
                                                </p>
                                            </div>
                                            <p className="font-bold text-blue-600">
                                                {tenantService.formatCurrency(unit.monthly_rent)}
                                            </p>
                                        </div>
                                    </button>
                                ))}
                            </div>
                        )}
                    </div>
                )}

                {/* Step 3: Lease Terms */}
                {currentStep === 3 && (
                    <div>
                        <h2 className="text-lg font-semibold text-gray-900 mb-4">Lease Terms</h2>
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div className="md:col-span-2 grid grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">
                                        Start Date <span className="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="date"
                                        name="start_date"
                                        value={formData.start_date}
                                        onChange={handleChange}
                                        className={`w-full px-4 py-2.5 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 ${errors.start_date ? 'border-red-300' : 'border-gray-200'
                                            }`}
                                    />
                                    {errors.start_date && (
                                        <p className="text-red-500 text-sm mt-1">{errors.start_date}</p>
                                    )}
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">
                                        End Date <span className="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="date"
                                        name="end_date"
                                        value={formData.end_date}
                                        onChange={handleChange}
                                        className={`w-full px-4 py-2.5 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 ${errors.end_date ? 'border-red-300' : 'border-gray-200'
                                            }`}
                                    />
                                    {errors.end_date && (
                                        <p className="text-red-500 text-sm mt-1">{errors.end_date}</p>
                                    )}
                                </div>
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                    Monthly Rent (KES) <span className="text-red-500">*</span>
                                </label>
                                <input
                                    type="number"
                                    name="monthly_rent"
                                    value={formData.monthly_rent}
                                    onChange={handleChange}
                                    className={`w-full px-4 py-2.5 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 ${errors.monthly_rent ? 'border-red-300' : 'border-gray-200'
                                        }`}
                                    placeholder="50000"
                                />
                                {errors.monthly_rent && (
                                    <p className="text-red-500 text-sm mt-1">{errors.monthly_rent}</p>
                                )}
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                    Security Deposit (KES)
                                </label>
                                <input
                                    type="number"
                                    name="deposit_amount"
                                    value={formData.deposit_amount}
                                    onChange={handleChange}
                                    className="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="50000"
                                />
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                    Payment Due Day
                                </label>
                                <select
                                    name="payment_due_day"
                                    value={formData.payment_due_day}
                                    onChange={handleChange}
                                    className="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                >
                                    {[...Array(28)].map((_, i) => (
                                        <option key={i + 1} value={i + 1}>
                                            {i + 1}
                                            {i + 1 === 1 ? 'st' : i + 1 === 2 ? 'nd' : i + 1 === 3 ? 'rd' : 'th'} of each month
                                        </option>
                                    ))}
                                </select>
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                    Payment Type
                                </label>
                                <select
                                    name="payment_type"
                                    value={formData.payment_type}
                                    onChange={handleChange}
                                    className="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                >
                                    <option value="manual">Manual Payment</option>
                                    <option value="recurring">Recurring (Auto-charge)</option>
                                </select>
                            </div>

                            <div className="md:col-span-2">
                                <label className="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                                <textarea
                                    name="notes"
                                    value={formData.notes}
                                    onChange={handleChange}
                                    rows={3}
                                    className="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Additional notes about this lease..."
                                />
                            </div>
                        </div>

                        {/* Pro-rated Calculation Preview */}
                        {formData.start_date && formData.monthly_rent && (
                            <div className="mt-6">
                                <ProRatedCalculation
                                    startDate={formData.start_date}
                                    monthlyRent={parseFloat(formData.monthly_rent) || 0}
                                    depositAmount={parseFloat(formData.deposit_amount) || 0}
                                    showDeposit={true}
                                />
                            </div>
                        )}
                    </div>
                )}

                {/* Step 4: Review */}
                {currentStep === 4 && (
                    <div>
                        <h2 className="text-lg font-semibold text-gray-900 mb-4">Review & Confirm</h2>

                        <div className="space-y-6">
                            {/* Tenant Summary */}
                            <div className="bg-gray-50 rounded-lg p-4">
                                <div className="flex items-center justify-between mb-2">
                                    <h3 className="text-sm font-medium text-gray-500">Tenant</h3>
                                    {!isEditing && (
                                        <button
                                            type="button"
                                            onClick={() => setCurrentStep(1)}
                                            className="text-sm text-blue-600 hover:text-blue-700"
                                        >
                                            Change
                                        </button>
                                    )}
                                </div>
                                <div className="flex items-center gap-3">
                                    <div className="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 font-semibold">
                                        {selectedTenant?.name?.charAt(0).toUpperCase()}
                                    </div>
                                    <div>
                                        <p className="font-medium text-gray-900">{selectedTenant?.name}</p>
                                        <p className="text-sm text-gray-500">{selectedTenant?.email}</p>
                                    </div>
                                </div>
                            </div>

                            {/* Unit Summary */}
                            <div className="bg-gray-50 rounded-lg p-4">
                                <div className="flex items-center justify-between mb-2">
                                    <h3 className="text-sm font-medium text-gray-500">Unit</h3>
                                    {!isEditing && (
                                        <button
                                            type="button"
                                            onClick={() => setCurrentStep(2)}
                                            className="text-sm text-blue-600 hover:text-blue-700"
                                        >
                                            Change
                                        </button>
                                    )}
                                </div>
                                <p className="font-medium text-gray-900">Unit {selectedUnit?.unit_number}</p>
                                <p className="text-sm text-gray-500">{selectedUnit?.property?.property_name}</p>
                            </div>

                            {/* Lease Terms Summary */}
                            <div className="bg-gray-50 rounded-lg p-4">
                                <div className="flex items-center justify-between mb-3">
                                    <h3 className="text-sm font-medium text-gray-500">Lease Terms</h3>
                                    <button
                                        type="button"
                                        onClick={() => setCurrentStep(3)}
                                        className="text-sm text-blue-600 hover:text-blue-700"
                                    >
                                        Edit
                                    </button>
                                </div>
                                <div className="grid grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <p className="text-gray-500">Start Date</p>
                                        <p className="font-medium text-gray-900">{formatDate(formData.start_date)}</p>
                                    </div>
                                    <div>
                                        <p className="text-gray-500">End Date</p>
                                        <p className="font-medium text-gray-900">{formatDate(formData.end_date)}</p>
                                    </div>
                                    <div>
                                        <p className="text-gray-500">Monthly Rent</p>
                                        <p className="font-medium text-gray-900">
                                            {tenantService.formatCurrency(formData.monthly_rent)}
                                        </p>
                                    </div>
                                    <div>
                                        <p className="text-gray-500">Security Deposit</p>
                                        <p className="font-medium text-gray-900">
                                            {tenantService.formatCurrency(formData.deposit_amount)}
                                        </p>
                                    </div>
                                    <div>
                                        <p className="text-gray-500">Payment Due</p>
                                        <p className="font-medium text-gray-900">
                                            {formData.payment_due_day}
                                            {formData.payment_due_day === 1
                                                ? 'st'
                                                : formData.payment_due_day === 2
                                                    ? 'nd'
                                                    : formData.payment_due_day === 3
                                                        ? 'rd'
                                                        : 'th'}{' '}
                                            of each month
                                        </p>
                                    </div>
                                    <div>
                                        <p className="text-gray-500">Payment Type</p>
                                        <p className="font-medium text-gray-900">
                                            {formData.payment_type === 'recurring' ? 'Recurring' : 'Manual'}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {/* First Payment Breakdown */}
                            {formData.start_date && formData.monthly_rent && (
                                <ProRatedCalculation
                                    startDate={formData.start_date}
                                    monthlyRent={parseFloat(formData.monthly_rent) || 0}
                                    depositAmount={parseFloat(formData.deposit_amount) || 0}
                                    showDeposit={true}
                                />
                            )}
                        </div>
                    </div>
                )}

                {/* Navigation Buttons */}
                <div className="flex justify-between mt-8 pt-6 border-t border-gray-100">
                    {currentStep > 1 ? (
                        <button
                            type="button"
                            onClick={prevStep}
                            className="px-6 py-2.5 text-gray-700 bg-gray-100 font-medium rounded-lg hover:bg-gray-200 transition-colors"
                        >
                            Back
                        </button>
                    ) : (
                        <Link
                            to="/company/leases"
                            className="px-6 py-2.5 text-gray-700 bg-gray-100 font-medium rounded-lg hover:bg-gray-200 transition-colors"
                        >
                            Cancel
                        </Link>
                    )}

                    {currentStep < 4 ? (
                        <button
                            type="button"
                            onClick={nextStep}
                            className="px-6 py-2.5 text-white bg-blue-600 font-medium rounded-lg hover:bg-blue-700 transition-colors"
                        >
                            Continue
                        </button>
                    ) : (
                        <button
                            type="button"
                            onClick={handleSubmit}
                            disabled={submitting}
                            className="px-6 py-2.5 text-white bg-green-600 font-medium rounded-lg hover:bg-green-700 disabled:opacity-50 transition-colors flex items-center gap-2"
                        >
                            {submitting ? (
                                <>
                                    <div className="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                                    <span>Creating...</span>
                                </>
                            ) : (
                                <>
                                    <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span>{isEditing ? 'Update Lease' : 'Create Lease'}</span>
                                </>
                            )}
                        </button>
                    )}
                </div>
            </div>
        </div>
    );
};

export default LeaseForm;
