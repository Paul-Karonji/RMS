import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { toast } from 'react-toastify';
import tenantService from '../../services/tenantService';
import LeaseCard from '../../components/tenants/LeaseCard';
import LeaseStatusBadge from '../../components/tenants/LeaseStatusBadge';

/**
 * Leases - Lease list page with filtering and actions
 */
const Leases = () => {
    const [leases, setLeases] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [viewMode, setViewMode] = useState('grid'); // 'grid' or 'table'

    // Filters
    const [filters, setFilters] = useState({
        search: '',
        status: '',
        page: 1,
        per_page: 12,
    });
    const [meta, setMeta] = useState({ total: 0, last_page: 1 });

    // Modals
    const [terminateModal, setTerminateModal] = useState({ open: false, lease: null });
    const [terminateData, setTerminateData] = useState({ termination_reason: '', termination_date: '' });
    const [processing, setProcessing] = useState(false);

    useEffect(() => {
        fetchLeases();
    }, [filters.page, filters.status]);

    const fetchLeases = async () => {
        setLoading(true);
        setError(null);
        try {
            const response = await tenantService.getLeases(filters);
            setLeases(response.data || []);
            setMeta(response.meta || { total: 0, last_page: 1 });
        } catch (err) {
            setError(err.message || 'Failed to fetch leases');
            toast.error('Failed to load leases');
        } finally {
            setLoading(false);
        }
    };

    const handleSearch = (e) => {
        e.preventDefault();
        setFilters({ ...filters, page: 1 });
        fetchLeases();
    };

    const handleTerminateClick = (lease) => {
        setTerminateModal({ open: true, lease });
        setTerminateData({
            termination_reason: '',
            termination_date: new Date().toISOString().split('T')[0],
        });
    };

    const handleTerminateConfirm = async () => {
        if (!terminateModal.lease) return;
        setProcessing(true);
        try {
            await tenantService.terminateLease(terminateModal.lease.id, terminateData);
            toast.success('Lease terminated successfully');
            setTerminateModal({ open: false, lease: null });
            fetchLeases();
        } catch (err) {
            toast.error(err.message || 'Failed to terminate lease');
        } finally {
            setProcessing(false);
        }
    };

    const handleRenewClick = (lease) => {
        // Navigate to lease form with renewal mode
        window.location.href = `/company/leases/${lease.id}/edit?renew=true`;
    };

    const formatDate = (dateString) => {
        if (!dateString) return '-';
        return new Date(dateString).toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
        });
    };

    return (
        <div className="space-y-6">
            {/* Header */}
            <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 className="text-2xl font-bold text-gray-900">Leases</h1>
                    <p className="text-gray-500 mt-1">Manage rental lease agreements</p>
                </div>
                <Link
                    to="/company/leases/create"
                    className="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2"
                >
                    <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
                    </svg>
                    Create Lease
                </Link>
            </div>

            {/* Filters */}
            <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                <form onSubmit={handleSearch} className="flex flex-col sm:flex-row gap-4">
                    {/* Search */}
                    <div className="flex-1">
                        <div className="relative">
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
                                placeholder="Search by tenant or unit..."
                                value={filters.search}
                                onChange={(e) => setFilters({ ...filters, search: e.target.value })}
                                className="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            />
                        </div>
                    </div>

                    {/* Status Filter */}
                    <select
                        value={filters.status}
                        onChange={(e) => setFilters({ ...filters, status: e.target.value, page: 1 })}
                        className="px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="terminated">Terminated</option>
                        <option value="expired">Expired</option>
                    </select>

                    {/* View Toggle */}
                    <div className="flex items-center border border-gray-200 rounded-lg overflow-hidden">
                        <button
                            type="button"
                            onClick={() => setViewMode('grid')}
                            className={`p-2.5 ${viewMode === 'grid' ? 'bg-blue-50 text-blue-600' : 'text-gray-400 hover:text-gray-600'}`}
                        >
                            <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth={2}
                                    d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"
                                />
                            </svg>
                        </button>
                        <button
                            type="button"
                            onClick={() => setViewMode('table')}
                            className={`p-2.5 ${viewMode === 'table' ? 'bg-blue-50 text-blue-600' : 'text-gray-400 hover:text-gray-600'}`}
                        >
                            <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth={2}
                                    d="M4 6h16M4 10h16M4 14h16M4 18h16"
                                />
                            </svg>
                        </button>
                    </div>

                    <button
                        type="submit"
                        className="px-4 py-2.5 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition-colors"
                    >
                        Search
                    </button>
                </form>
            </div>

            {/* Stats */}
            <div className="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                    <p className="text-sm text-gray-500">Total Leases</p>
                    <p className="text-2xl font-bold text-gray-900">{meta.total}</p>
                </div>
                <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                    <p className="text-sm text-gray-500">Active</p>
                    <p className="text-2xl font-bold text-green-600">
                        {leases.filter((l) => l.status === 'active').length}
                    </p>
                </div>
                <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                    <p className="text-sm text-gray-500">Expiring Soon</p>
                    <p className="text-2xl font-bold text-orange-600">
                        {leases.filter((l) => {
                            if (l.status !== 'active') return false;
                            const end = new Date(l.end_date);
                            const now = new Date();
                            const days = Math.ceil((end - now) / (1000 * 60 * 60 * 24));
                            return days <= 60 && days > 0;
                        }).length}
                    </p>
                </div>
                <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                    <p className="text-sm text-gray-500">Terminated</p>
                    <p className="text-2xl font-bold text-red-600">
                        {leases.filter((l) => l.status === 'terminated').length}
                    </p>
                </div>
            </div>

            {/* Loading */}
            {loading && (
                <div className="flex justify-center py-12">
                    <div className="w-8 h-8 border-4 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
                </div>
            )}

            {/* Error */}
            {error && !loading && (
                <div className="bg-red-50 border border-red-200 rounded-lg p-4 text-red-700">{error}</div>
            )}

            {/* Empty State */}
            {!loading && !error && leases.length === 0 && (
                <div className="text-center py-12 bg-white rounded-xl shadow-sm border border-gray-100">
                    <svg
                        className="w-16 h-16 text-gray-300 mx-auto mb-4"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            strokeWidth={1}
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                        />
                    </svg>
                    <h3 className="text-lg font-medium text-gray-900 mb-1">No leases found</h3>
                    <p className="text-gray-500 mb-4">Get started by creating your first lease</p>
                    <Link
                        to="/company/leases/create"
                        className="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors"
                    >
                        <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
                        </svg>
                        Create Lease
                    </Link>
                </div>
            )}

            {/* Grid View */}
            {!loading && !error && leases.length > 0 && viewMode === 'grid' && (
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    {leases.map((lease) => (
                        <LeaseCard
                            key={lease.id}
                            lease={lease}
                            onTerminate={handleTerminateClick}
                            onRenew={handleRenewClick}
                        />
                    ))}
                </div>
            )}

            {/* Table View */}
            {!loading && !error && leases.length > 0 && viewMode === 'table' && (
                <div className="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full">
                            <thead className="bg-gray-50 border-b border-gray-100">
                                <tr>
                                    <th className="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Lease
                                    </th>
                                    <th className="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Tenant
                                    </th>
                                    <th className="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Unit
                                    </th>
                                    <th className="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Duration
                                    </th>
                                    <th className="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Rent
                                    </th>
                                    <th className="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th className="text-right px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-100">
                                {leases.map((lease) => (
                                    <tr key={lease.id} className="hover:bg-gray-50">
                                        <td className="px-6 py-4">
                                            <span className="font-mono text-sm text-gray-500">{lease.lease_number}</span>
                                        </td>
                                        <td className="px-6 py-4">
                                            <p className="font-medium text-gray-900">{lease.tenant?.name}</p>
                                        </td>
                                        <td className="px-6 py-4">
                                            <p className="text-sm text-gray-900">Unit {lease.unit?.unit_number}</p>
                                            <p className="text-sm text-gray-500">{lease.property?.property_name}</p>
                                        </td>
                                        <td className="px-6 py-4 text-sm text-gray-600">
                                            {formatDate(lease.start_date)} - {formatDate(lease.end_date)}
                                        </td>
                                        <td className="px-6 py-4 font-semibold text-gray-900">
                                            {tenantService.formatCurrency(lease.monthly_rent)}
                                        </td>
                                        <td className="px-6 py-4">
                                            <LeaseStatusBadge status={lease.status} endDate={lease.end_date} />
                                        </td>
                                        <td className="px-6 py-4 text-right">
                                            <div className="flex items-center justify-end gap-2">
                                                <Link
                                                    to={`/company/leases/${lease.id}/edit`}
                                                    className="p-1.5 text-gray-400 hover:text-blue-600 transition-colors"
                                                >
                                                    <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path
                                                            strokeLinecap="round"
                                                            strokeLinejoin="round"
                                                            strokeWidth={2}
                                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                                        />
                                                        <path
                                                            strokeLinecap="round"
                                                            strokeLinejoin="round"
                                                            strokeWidth={2}
                                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                                        />
                                                    </svg>
                                                </Link>
                                                {lease.status === 'active' && (
                                                    <>
                                                        <button
                                                            onClick={() => handleRenewClick(lease)}
                                                            className="p-1.5 text-gray-400 hover:text-green-600 transition-colors"
                                                        >
                                                            <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path
                                                                    strokeLinecap="round"
                                                                    strokeLinejoin="round"
                                                                    strokeWidth={2}
                                                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                                                                />
                                                            </svg>
                                                        </button>
                                                        <button
                                                            onClick={() => handleTerminateClick(lease)}
                                                            className="p-1.5 text-gray-400 hover:text-red-600 transition-colors"
                                                        >
                                                            <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path
                                                                    strokeLinecap="round"
                                                                    strokeLinejoin="round"
                                                                    strokeWidth={2}
                                                                    d="M6 18L18 6M6 6l12 12"
                                                                />
                                                            </svg>
                                                        </button>
                                                    </>
                                                )}
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>
            )}

            {/* Pagination */}
            {!loading && meta.last_page > 1 && (
                <div className="flex items-center justify-between bg-white rounded-xl shadow-sm border border-gray-100 px-4 py-3">
                    <p className="text-sm text-gray-500">
                        Showing {(filters.page - 1) * filters.per_page + 1} to{' '}
                        {Math.min(filters.page * filters.per_page, meta.total)} of {meta.total} leases
                    </p>
                    <div className="flex items-center gap-2">
                        <button
                            onClick={() => setFilters({ ...filters, page: filters.page - 1 })}
                            disabled={filters.page === 1}
                            className="px-3 py-1.5 text-sm font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        >
                            Previous
                        </button>
                        <span className="text-sm text-gray-600">
                            Page {filters.page} of {meta.last_page}
                        </span>
                        <button
                            onClick={() => setFilters({ ...filters, page: filters.page + 1 })}
                            disabled={filters.page === meta.last_page}
                            className="px-3 py-1.5 text-sm font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        >
                            Next
                        </button>
                    </div>
                </div>
            )}

            {/* Terminate Modal */}
            {terminateModal.open && (
                <div className="fixed inset-0 z-50 overflow-y-auto">
                    <div
                        className="fixed inset-0 bg-black bg-opacity-50"
                        onClick={() => setTerminateModal({ open: false, lease: null })}
                    ></div>
                    <div className="flex min-h-full items-center justify-center p-4">
                        <div className="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
                            <h3 className="text-lg font-semibold text-gray-900 mb-2">Terminate Lease</h3>
                            <p className="text-gray-600 mb-4">
                                Are you sure you want to terminate the lease for{' '}
                                <strong>{terminateModal.lease?.tenant?.name}</strong>?
                            </p>
                            <div className="space-y-4 mb-6">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">
                                        Termination Date
                                    </label>
                                    <input
                                        type="date"
                                        value={terminateData.termination_date}
                                        onChange={(e) =>
                                            setTerminateData({ ...terminateData, termination_date: e.target.value })
                                        }
                                        className="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    />
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">
                                        Reason (optional)
                                    </label>
                                    <textarea
                                        value={terminateData.termination_reason}
                                        onChange={(e) =>
                                            setTerminateData({ ...terminateData, termination_reason: e.target.value })
                                        }
                                        rows={3}
                                        className="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="Reason for termination..."
                                    />
                                </div>
                            </div>
                            <div className="flex gap-3">
                                <button
                                    onClick={() => setTerminateModal({ open: false, lease: null })}
                                    className="flex-1 px-4 py-2 text-gray-700 bg-gray-100 font-medium rounded-lg hover:bg-gray-200 transition-colors"
                                >
                                    Cancel
                                </button>
                                <button
                                    onClick={handleTerminateConfirm}
                                    disabled={processing}
                                    className="flex-1 px-4 py-2 text-white bg-red-600 font-medium rounded-lg hover:bg-red-700 disabled:opacity-50 transition-colors"
                                >
                                    {processing ? 'Terminating...' : 'Terminate Lease'}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
};

export default Leases;
