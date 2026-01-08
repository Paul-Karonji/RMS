import React, { useState, useEffect } from 'react';
import { useParams, Link, useNavigate } from 'react-router-dom';
import { toast } from 'react-toastify';
import tenantService from '../../services/tenantService';
import TenantStatusBadge from '../../components/tenants/TenantStatusBadge';
import LeaseStatusBadge from '../../components/tenants/LeaseStatusBadge';

/**
 * TenantDetails - View tenant details with lease history
 */
const TenantDetails = () => {
    const { id } = useParams();
    const navigate = useNavigate();
    const [tenant, setTenant] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        fetchTenant();
    }, [id]);

    const fetchTenant = async () => {
        setLoading(true);
        setError(null);
        try {
            const response = await tenantService.getTenant(id);
            setTenant(response.data);
        } catch (err) {
            setError(err.message || 'Failed to load tenant');
            toast.error('Failed to load tenant details');
        } finally {
            setLoading(false);
        }
    };

    const formatDate = (dateString) => {
        if (!dateString) return '-';
        return new Date(dateString).toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
        });
    };

    if (loading) {
        return (
            <div className="flex justify-center py-12">
                <div className="w-8 h-8 border-4 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
            </div>
        );
    }

    if (error || !tenant) {
        return (
            <div className="text-center py-12">
                <p className="text-red-600 mb-4">{error || 'Tenant not found'}</p>
                <Link
                    to="/company/tenants"
                    className="text-blue-600 hover:text-blue-700 font-medium"
                >
                    Back to Tenants
                </Link>
            </div>
        );
    }

    return (
        <div className="space-y-6">
            {/* Header */}
            <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <Link
                        to="/company/tenants"
                        className="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 mb-2"
                    >
                        <svg className="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
                        </svg>
                        Back to Tenants
                    </Link>
                    <div className="flex items-center gap-3">
                        <h1 className="text-2xl font-bold text-gray-900">{tenant.name}</h1>
                        <TenantStatusBadge status={tenant.is_active ? 'active' : 'inactive'} />
                    </div>
                </div>
                <div className="flex items-center gap-3">
                    <Link
                        to={`/company/tenants/${id}/edit`}
                        className="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors flex items-center gap-2"
                    >
                        <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path
                                strokeLinecap="round"
                                strokeLinejoin="round"
                                strokeWidth={2}
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                            />
                        </svg>
                        Edit
                    </Link>
                    {!tenant.current_lease && (
                        <Link
                            to={`/company/leases/create?tenant_id=${id}`}
                            className="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2"
                        >
                            <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
                            </svg>
                            Create Lease
                        </Link>
                    )}
                </div>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {/* Main Info */}
                <div className="lg:col-span-2 space-y-6">
                    {/* Contact Information */}
                    <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h2 className="text-lg font-semibold text-gray-900 mb-4">Contact Information</h2>
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div className="flex items-start gap-3">
                                <div className="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <svg className="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            strokeWidth={2}
                                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                                        />
                                    </svg>
                                </div>
                                <div>
                                    <p className="text-sm text-gray-500">Email</p>
                                    <p className="font-medium text-gray-900">{tenant.email}</p>
                                </div>
                            </div>
                            <div className="flex items-start gap-3">
                                <div className="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <svg className="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            strokeWidth={2}
                                            d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"
                                        />
                                    </svg>
                                </div>
                                <div>
                                    <p className="text-sm text-gray-500">Phone</p>
                                    <p className="font-medium text-gray-900">{tenant.phone}</p>
                                </div>
                            </div>
                            {tenant.id_number && (
                                <div className="flex items-start gap-3">
                                    <div className="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <svg className="w-5 h-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path
                                                strokeLinecap="round"
                                                strokeLinejoin="round"
                                                strokeWidth={2}
                                                d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"
                                            />
                                        </svg>
                                    </div>
                                    <div>
                                        <p className="text-sm text-gray-500">ID Number</p>
                                        <p className="font-medium text-gray-900">{tenant.id_number}</p>
                                    </div>
                                </div>
                            )}
                        </div>
                    </div>

                    {/* Emergency Contact */}
                    {(tenant.emergency_contact_name || tenant.emergency_contact_phone) && (
                        <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                            <h2 className="text-lg font-semibold text-gray-900 mb-4">Emergency Contact</h2>
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {tenant.emergency_contact_name && (
                                    <div>
                                        <p className="text-sm text-gray-500">Name</p>
                                        <p className="font-medium text-gray-900">{tenant.emergency_contact_name}</p>
                                    </div>
                                )}
                                {tenant.emergency_contact_phone && (
                                    <div>
                                        <p className="text-sm text-gray-500">Phone</p>
                                        <p className="font-medium text-gray-900">{tenant.emergency_contact_phone}</p>
                                    </div>
                                )}
                            </div>
                        </div>
                    )}

                    {/* Employment */}
                    {(tenant.occupation || tenant.employer) && (
                        <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                            <h2 className="text-lg font-semibold text-gray-900 mb-4">Employment</h2>
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {tenant.occupation && (
                                    <div>
                                        <p className="text-sm text-gray-500">Occupation</p>
                                        <p className="font-medium text-gray-900">{tenant.occupation}</p>
                                    </div>
                                )}
                                {tenant.employer && (
                                    <div>
                                        <p className="text-sm text-gray-500">Employer</p>
                                        <p className="font-medium text-gray-900">{tenant.employer}</p>
                                    </div>
                                )}
                            </div>
                        </div>
                    )}

                    {/* Lease History */}
                    <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h2 className="text-lg font-semibold text-gray-900 mb-4">Lease History</h2>
                        {tenant.leases && tenant.leases.length > 0 ? (
                            <div className="space-y-3">
                                {tenant.leases.map((lease) => (
                                    <div
                                        key={lease.id}
                                        className="flex items-center justify-between p-4 bg-gray-50 rounded-lg"
                                    >
                                        <div className="flex items-center gap-4">
                                            <div className="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                                <svg
                                                    className="w-5 h-5 text-blue-600"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                    stroke="currentColor"
                                                >
                                                    <path
                                                        strokeLinecap="round"
                                                        strokeLinejoin="round"
                                                        strokeWidth={2}
                                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                                    />
                                                </svg>
                                            </div>
                                            <div>
                                                <p className="font-medium text-gray-900">
                                                    Unit {lease.unit?.unit_number} • {lease.property?.property_name}
                                                </p>
                                                <p className="text-sm text-gray-500">
                                                    {formatDate(lease.start_date)} - {formatDate(lease.end_date)}
                                                </p>
                                            </div>
                                        </div>
                                        <div className="flex items-center gap-3">
                                            <LeaseStatusBadge status={lease.status} endDate={lease.end_date} />
                                            <span className="font-semibold text-gray-900">
                                                {tenantService.formatCurrency(lease.monthly_rent)}
                                            </span>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <p className="text-gray-500 text-center py-4">No lease history</p>
                        )}
                    </div>
                </div>

                {/* Sidebar */}
                <div className="space-y-6">
                    {/* Current Lease */}
                    {tenant.current_lease && (
                        <div className="bg-gradient-to-br from-blue-600 to-indigo-700 rounded-xl p-6 text-white">
                            <h3 className="text-sm font-medium text-blue-100 mb-3">Current Lease</h3>
                            <p className="text-2xl font-bold mb-1">
                                Unit {tenant.current_lease.unit?.unit_number}
                            </p>
                            <p className="text-blue-100 text-sm mb-4">
                                {tenant.current_lease.property?.property_name}
                            </p>
                            <div className="bg-white/10 rounded-lg p-3 mb-4">
                                <p className="text-sm text-blue-100">Monthly Rent</p>
                                <p className="text-xl font-bold">
                                    {tenantService.formatCurrency(tenant.current_lease.monthly_rent)}
                                </p>
                            </div>
                            <div className="grid grid-cols-2 gap-3 text-sm">
                                <div>
                                    <p className="text-blue-100">Start</p>
                                    <p className="font-medium">{formatDate(tenant.current_lease.start_date)}</p>
                                </div>
                                <div>
                                    <p className="text-blue-100">End</p>
                                    <p className="font-medium">{formatDate(tenant.current_lease.end_date)}</p>
                                </div>
                            </div>
                        </div>
                    )}

                    {/* Quick Actions */}
                    <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h3 className="text-sm font-medium text-gray-900 mb-4">Quick Actions</h3>
                        <div className="space-y-2">
                            <Link
                                to={`/company/tenants/${id}/edit`}
                                className="w-full flex items-center gap-3 px-4 py-2.5 text-gray-700 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors"
                            >
                                <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth={2}
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                    />
                                </svg>
                                Edit Tenant
                            </Link>
                            {!tenant.current_lease && (
                                <Link
                                    to={`/company/leases/create?tenant_id=${id}`}
                                    className="w-full flex items-center gap-3 px-4 py-2.5 text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors"
                                >
                                    <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
                                    </svg>
                                    Create Lease
                                </Link>
                            )}
                            {tenant.current_lease && (
                                <Link
                                    to={`/company/leases/${tenant.current_lease.id}/edit`}
                                    className="w-full flex items-center gap-3 px-4 py-2.5 text-gray-700 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors"
                                >
                                    <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            strokeWidth={2}
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                        />
                                    </svg>
                                    View Lease
                                </Link>
                            )}
                        </div>
                    </div>

                    {/* Tenant Info */}
                    <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h3 className="text-sm font-medium text-gray-900 mb-4">Account Info</h3>
                        <div className="space-y-3 text-sm">
                            <div className="flex justify-between">
                                <span className="text-gray-500">Created</span>
                                <span className="font-medium text-gray-900">{formatDate(tenant.created_at)}</span>
                            </div>
                            <div className="flex justify-between">
                                <span className="text-gray-500">Last Updated</span>
                                <span className="font-medium text-gray-900">{formatDate(tenant.updated_at)}</span>
                            </div>
                            <div className="flex justify-between">
                                <span className="text-gray-500">Status</span>
                                <TenantStatusBadge status={tenant.is_active ? 'active' : 'inactive'} />
                            </div>
                        </div>
                    </div>

                    {/* Notes */}
                    {tenant.notes && (
                        <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                            <h3 className="text-sm font-medium text-gray-900 mb-3">Notes</h3>
                            <p className="text-sm text-gray-600">{tenant.notes}</p>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
};

export default TenantDetails;
