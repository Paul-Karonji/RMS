import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { toast } from 'react-toastify';
import tenantService from '../../services/tenantService';
import InquiryApproval from '../../components/tenants/InquiryApproval';

/**
 * Inquiries - Pending inquiry list for approval
 */
const Inquiries = () => {
    const [inquiries, setInquiries] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [selectedInquiry, setSelectedInquiry] = useState(null);
    const [filters, setFilters] = useState({
        status: 'pending',
        search: '',
        page: 1,
        per_page: 15,
    });
    const [meta, setMeta] = useState({ total: 0, last_page: 1 });

    useEffect(() => {
        fetchInquiries();
    }, [filters.page, filters.status]);

    const fetchInquiries = async () => {
        setLoading(true);
        setError(null);
        try {
            const response = await tenantService.getInquiries(filters);
            setInquiries(response.data || []);
            setMeta(response.meta || { total: 0, last_page: 1 });
        } catch (err) {
            setError(err.message || 'Failed to fetch inquiries');
            toast.error('Failed to load inquiries');
        } finally {
            setLoading(false);
        }
    };

    const handleSearch = (e) => {
        e.preventDefault();
        setFilters({ ...filters, page: 1 });
        fetchInquiries();
    };

    const handleApproveSuccess = (result) => {
        toast.success('Inquiry approved successfully');
        fetchInquiries();
    };

    const handleRejectSuccess = () => {
        toast.success('Inquiry rejected');
        fetchInquiries();
    };

    const formatDate = (dateString) => {
        if (!dateString) return '-';
        return new Date(dateString).toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
        });
    };

    const getStatusBadge = (status) => {
        switch (status) {
            case 'pending':
                return <span className="px-2.5 py-1 text-xs font-medium text-yellow-800 bg-yellow-100 rounded-full">Pending</span>;
            case 'approved':
                return <span className="px-2.5 py-1 text-xs font-medium text-green-800 bg-green-100 rounded-full">Approved</span>;
            case 'rejected':
                return <span className="px-2.5 py-1 text-xs font-medium text-red-800 bg-red-100 rounded-full">Rejected</span>;
            default:
                return <span className="px-2.5 py-1 text-xs font-medium text-gray-800 bg-gray-100 rounded-full">{status}</span>;
        }
    };

    return (
        <div className="space-y-6">
            {/* Header */}
            <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 className="text-2xl font-bold text-gray-900">Rental Inquiries</h1>
                    <p className="text-gray-500 mt-1">Review and approve inquiries from potential tenants</p>
                </div>
                <Link
                    to="/company/tenants"
                    className="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                >
                    Back to Tenants
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
                                placeholder="Search by name, email, or phone..."
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
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>

                    <button
                        type="submit"
                        className="px-4 py-2.5 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition-colors"
                    >
                        Search
                    </button>
                </form>
            </div>

            {/* Stats */}
            <div className="grid grid-cols-3 gap-4">
                <div className="bg-yellow-50 border border-yellow-100 rounded-xl p-4">
                    <p className="text-sm text-yellow-600">Pending</p>
                    <p className="text-2xl font-bold text-yellow-700">
                        {inquiries.filter((i) => i.status === 'pending').length}
                    </p>
                </div>
                <div className="bg-green-50 border border-green-100 rounded-xl p-4">
                    <p className="text-sm text-green-600">Approved</p>
                    <p className="text-2xl font-bold text-green-700">
                        {inquiries.filter((i) => i.status === 'approved').length}
                    </p>
                </div>
                <div className="bg-red-50 border border-red-100 rounded-xl p-4">
                    <p className="text-sm text-red-600">Rejected</p>
                    <p className="text-2xl font-bold text-red-700">
                        {inquiries.filter((i) => i.status === 'rejected').length}
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
                <div className="bg-red-50 border border-red-200 rounded-lg p-4 text-red-700">
                    {error}
                </div>
            )}

            {/* Empty State */}
            {!loading && !error && inquiries.length === 0 && (
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
                            d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"
                        />
                    </svg>
                    <h3 className="text-lg font-medium text-gray-900 mb-1">No inquiries found</h3>
                    <p className="text-gray-500">
                        {filters.status === 'pending'
                            ? 'There are no pending inquiries to review'
                            : 'No inquiries match your filters'}
                    </p>
                </div>
            )}

            {/* Inquiries List */}
            {!loading && !error && inquiries.length > 0 && (
                <div className="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full">
                            <thead className="bg-gray-50 border-b border-gray-100">
                                <tr>
                                    <th className="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Prospect
                                    </th>
                                    <th className="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Unit
                                    </th>
                                    <th className="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Move-in Date
                                    </th>
                                    <th className="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Submitted
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
                                {inquiries.map((inquiry) => (
                                    <tr key={inquiry.id} className="hover:bg-gray-50">
                                        <td className="px-6 py-4">
                                            <div>
                                                <p className="font-medium text-gray-900">{inquiry.full_name}</p>
                                                <p className="text-sm text-gray-500">{inquiry.email}</p>
                                                <p className="text-sm text-gray-400">{inquiry.phone}</p>
                                            </div>
                                        </td>
                                        <td className="px-6 py-4">
                                            <p className="text-sm text-gray-900">
                                                Unit {inquiry.unit?.unit_number}
                                            </p>
                                            <p className="text-sm text-gray-500">
                                                {inquiry.unit?.property?.property_name}
                                            </p>
                                        </td>
                                        <td className="px-6 py-4 text-sm text-gray-600">
                                            {formatDate(inquiry.move_in_date)}
                                        </td>
                                        <td className="px-6 py-4 text-sm text-gray-600">
                                            {formatDate(inquiry.created_at)}
                                        </td>
                                        <td className="px-6 py-4">
                                            {getStatusBadge(inquiry.status)}
                                        </td>
                                        <td className="px-6 py-4 text-right">
                                            {inquiry.status === 'pending' ? (
                                                <button
                                                    onClick={() => setSelectedInquiry(inquiry)}
                                                    className="px-4 py-1.5 text-sm font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors"
                                                >
                                                    Review
                                                </button>
                                            ) : (
                                                <button
                                                    onClick={() => setSelectedInquiry(inquiry)}
                                                    className="px-4 py-1.5 text-sm font-medium text-gray-600 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors"
                                                >
                                                    View
                                                </button>
                                            )}
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
                        {Math.min(filters.page * filters.per_page, meta.total)} of {meta.total} inquiries
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

            {/* Inquiry Approval Modal */}
            <InquiryApproval
                inquiry={selectedInquiry}
                isOpen={!!selectedInquiry}
                onClose={() => setSelectedInquiry(null)}
                onApprove={handleApproveSuccess}
                onReject={handleRejectSuccess}
            />
        </div>
    );
};

export default Inquiries;
