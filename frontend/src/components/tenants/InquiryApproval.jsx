import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import tenantService from '../../services/tenantService';

/**
 * InquiryApproval - Modal/Panel for approving or rejecting inquiries
 * @param {Object} inquiry - Inquiry data object
 * @param {boolean} isOpen - Whether modal is open
 * @param {Function} onClose - Close handler
 * @param {Function} onApprove - Approve success handler
 * @param {Function} onReject - Reject success handler
 */
const InquiryApproval = ({ inquiry, isOpen, onClose, onApprove, onReject }) => {
    const [isLoading, setIsLoading] = useState(false);
    const [showRejectForm, setShowRejectForm] = useState(false);
    const [rejectReason, setRejectReason] = useState('');
    const [approvalResult, setApprovalResult] = useState(null);
    const [error, setError] = useState(null);

    if (!isOpen || !inquiry) return null;

    const handleApprove = async () => {
        setIsLoading(true);
        setError(null);
        try {
            const result = await tenantService.approveInquiry(inquiry.id);
            setApprovalResult(result.data);
            if (onApprove) onApprove(result);
        } catch (err) {
            setError(err.message || 'Failed to approve inquiry');
        } finally {
            setIsLoading(false);
        }
    };

    const handleReject = async () => {
        if (!rejectReason.trim()) {
            setError('Please provide a rejection reason');
            return;
        }
        setIsLoading(true);
        setError(null);
        try {
            const result = await tenantService.rejectInquiry(inquiry.id, rejectReason);
            if (onReject) onReject(result);
            onClose();
        } catch (err) {
            setError(err.message || 'Failed to reject inquiry');
        } finally {
            setIsLoading(false);
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

    return (
        <div className="fixed inset-0 z-50 overflow-y-auto">
            {/* Backdrop */}
            <div
                className="fixed inset-0 bg-black bg-opacity-50 transition-opacity"
                onClick={onClose}
            ></div>

            {/* Modal */}
            <div className="flex min-h-full items-center justify-center p-4">
                <div className="relative bg-white rounded-2xl shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto">
                    {/* Header */}
                    <div className="sticky top-0 bg-white border-b border-gray-100 px-6 py-4 rounded-t-2xl">
                        <div className="flex items-center justify-between">
                            <h2 className="text-lg font-semibold text-gray-900">Inquiry Details</h2>
                            <button
                                onClick={onClose}
                                className="text-gray-400 hover:text-gray-600 transition-colors"
                            >
                                <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth={2}
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>

                    {/* Content */}
                    <div className="p-6">
                        {/* Success State */}
                        {approvalResult && (
                            <div className="text-center py-6">
                                <div className="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg className="w-8 h-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            strokeWidth={2}
                                            d="M5 13l4 4L19 7"
                                        />
                                    </svg>
                                </div>
                                <h3 className="text-lg font-semibold text-gray-900 mb-2">
                                    Tenant Account Created!
                                </h3>
                                <p className="text-gray-600 mb-4">
                                    The inquiry has been approved and a tenant account has been created.
                                </p>
                                <div className="bg-blue-50 rounded-lg p-4 text-left">
                                    <p className="text-sm text-gray-600 mb-2">Login Credentials:</p>
                                    <div className="space-y-1">
                                        <p className="text-sm">
                                            <span className="text-gray-500">Email:</span>{' '}
                                            <span className="font-mono text-gray-900">{approvalResult.credentials?.email}</span>
                                        </p>
                                        <p className="text-sm">
                                            <span className="text-gray-500">Password:</span>{' '}
                                            <span className="font-mono text-gray-900">{approvalResult.credentials?.temporary_password}</span>
                                        </p>
                                    </div>
                                    <p className="text-xs text-blue-600 mt-3">
                                        ⚠️ These credentials have been emailed to the tenant.
                                    </p>
                                </div>
                                <button
                                    onClick={onClose}
                                    className="mt-6 w-full px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors"
                                >
                                    Done
                                </button>
                            </div>
                        )}

                        {/* Normal State */}
                        {!approvalResult && (
                            <>
                                {/* Error */}
                                {error && (
                                    <div className="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
                                        {error}
                                    </div>
                                )}

                                {/* Inquiry Info */}
                                <div className="space-y-4">
                                    {/* Contact Details */}
                                    <div className="bg-gray-50 rounded-lg p-4">
                                        <h4 className="text-sm font-medium text-gray-500 mb-3">Contact Information</h4>
                                        <dl className="space-y-2">
                                            <div className="flex justify-between">
                                                <dt className="text-sm text-gray-500">Name</dt>
                                                <dd className="text-sm font-medium text-gray-900">{inquiry.full_name}</dd>
                                            </div>
                                            <div className="flex justify-between">
                                                <dt className="text-sm text-gray-500">Email</dt>
                                                <dd className="text-sm font-medium text-gray-900">{inquiry.email}</dd>
                                            </div>
                                            <div className="flex justify-between">
                                                <dt className="text-sm text-gray-500">Phone</dt>
                                                <dd className="text-sm font-medium text-gray-900">{inquiry.phone}</dd>
                                            </div>
                                        </dl>
                                    </div>

                                    {/* Unit Details */}
                                    <div className="bg-gray-50 rounded-lg p-4">
                                        <h4 className="text-sm font-medium text-gray-500 mb-3">Interested In</h4>
                                        <div className="flex items-center gap-3">
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
                                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                                                    />
                                                </svg>
                                            </div>
                                            <div>
                                                <p className="font-medium text-gray-900">
                                                    Unit {inquiry.unit?.unit_number}
                                                </p>
                                                <p className="text-sm text-gray-500">
                                                    {inquiry.unit?.property?.property_name}
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    {/* Move-in Date */}
                                    {inquiry.move_in_date && (
                                        <div className="bg-gray-50 rounded-lg p-4">
                                            <h4 className="text-sm font-medium text-gray-500 mb-2">Preferred Move-in Date</h4>
                                            <p className="text-sm font-medium text-gray-900">
                                                {formatDate(inquiry.move_in_date)}
                                            </p>
                                        </div>
                                    )}

                                    {/* Message */}
                                    {inquiry.message && (
                                        <div className="bg-gray-50 rounded-lg p-4">
                                            <h4 className="text-sm font-medium text-gray-500 mb-2">Message</h4>
                                            <p className="text-sm text-gray-700">{inquiry.message}</p>
                                        </div>
                                    )}

                                    {/* Submitted Date */}
                                    <p className="text-xs text-gray-400 text-center">
                                        Submitted on {formatDate(inquiry.created_at)}
                                    </p>
                                </div>

                                {/* Reject Form */}
                                {showRejectForm && (
                                    <div className="mt-4 p-4 bg-red-50 rounded-lg border border-red-100">
                                        <label className="block text-sm font-medium text-red-800 mb-2">
                                            Rejection Reason
                                        </label>
                                        <textarea
                                            value={rejectReason}
                                            onChange={(e) => setRejectReason(e.target.value)}
                                            className="w-full px-3 py-2 border border-red-200 rounded-lg text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                            rows={3}
                                            placeholder="Please explain why this inquiry is being rejected..."
                                        />
                                        <div className="flex gap-2 mt-3">
                                            <button
                                                onClick={handleReject}
                                                disabled={isLoading}
                                                className="flex-1 px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 disabled:opacity-50 transition-colors"
                                            >
                                                {isLoading ? 'Rejecting...' : 'Confirm Rejection'}
                                            </button>
                                            <button
                                                onClick={() => setShowRejectForm(false)}
                                                className="px-4 py-2 bg-white text-gray-700 text-sm font-medium rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors"
                                            >
                                                Cancel
                                            </button>
                                        </div>
                                    </div>
                                )}

                                {/* Action Buttons */}
                                {!showRejectForm && (
                                    <div className="flex gap-3 mt-6">
                                        <button
                                            onClick={handleApprove}
                                            disabled={isLoading}
                                            className="flex-1 px-4 py-2.5 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 disabled:opacity-50 transition-colors flex items-center justify-center gap-2"
                                        >
                                            {isLoading ? (
                                                <span>Processing...</span>
                                            ) : (
                                                <>
                                                    <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path
                                                            strokeLinecap="round"
                                                            strokeLinejoin="round"
                                                            strokeWidth={2}
                                                            d="M5 13l4 4L19 7"
                                                        />
                                                    </svg>
                                                    <span>Approve & Create Tenant</span>
                                                </>
                                            )}
                                        </button>
                                        <button
                                            onClick={() => setShowRejectForm(true)}
                                            disabled={isLoading}
                                            className="px-4 py-2.5 bg-red-50 text-red-600 font-medium rounded-lg hover:bg-red-100 disabled:opacity-50 transition-colors"
                                        >
                                            Reject
                                        </button>
                                    </div>
                                )}
                            </>
                        )}
                    </div>
                </div>
            </div>
        </div>
    );
};

export default InquiryApproval;
