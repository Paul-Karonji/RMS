import { useState } from 'react';
import { Button, Alert } from '../common';
import {
  CheckCircleIcon,
  XCircleIcon,
  XMarkIcon,
} from '@heroicons/react/24/outline';

const ApprovalActions = ({
  propertyId,
  currentStatus,
  onApprove,
  onReject,
  isLoading = false,
}) => {
  const [showRejectModal, setShowRejectModal] = useState(false);
  const [rejectionReason, setRejectionReason] = useState('');
  const [error, setError] = useState(null);

  const canApprove = currentStatus === 'pending_approval';

  const handleApprove = async () => {
    setError(null);
    try {
      await onApprove(propertyId);
    } catch (err) {
      setError(err.message || 'Failed to approve property');
    }
  };

  const handleReject = async () => {
    if (!rejectionReason.trim()) {
      setError('Please provide a rejection reason');
      return;
    }

    setError(null);
    try {
      await onReject(propertyId, rejectionReason);
      setShowRejectModal(false);
      setRejectionReason('');
    } catch (err) {
      setError(err.message || 'Failed to reject property');
    }
  };

  if (!canApprove) {
    return null;
  }

  return (
    <>
      <div className="flex items-center gap-3">
        <Button
          onClick={handleApprove}
          loading={isLoading}
          disabled={isLoading}
          className="bg-green-600 hover:bg-green-700 focus:ring-green-500"
        >
          <CheckCircleIcon className="w-5 h-5 mr-2" />
          Approve
        </Button>
        <Button
          variant="danger"
          onClick={() => setShowRejectModal(true)}
          disabled={isLoading}
        >
          <XCircleIcon className="w-5 h-5 mr-2" />
          Reject
        </Button>
      </div>

      {/* Rejection Modal */}
      {showRejectModal && (
        <div className="fixed inset-0 z-50 overflow-y-auto">
          <div
            className="fixed inset-0 bg-black bg-opacity-50 transition-opacity"
            onClick={() => setShowRejectModal(false)}
          />

          <div className="flex min-h-full items-center justify-center p-4">
            <div className="relative bg-surface rounded-lg shadow-xl max-w-md w-full p-6">
              {/* Modal Header */}
              <div className="flex items-center justify-between mb-4">
                <h3 className="text-lg font-semibold text-text">
                  Reject Property
                </h3>
                <button
                  onClick={() => setShowRejectModal(false)}
                  className="p-1 rounded hover:bg-slate-100"
                >
                  <XMarkIcon className="w-5 h-5 text-muted" />
                </button>
              </div>

              {/* Modal Body */}
              <div className="space-y-4">
                {error && <Alert type="error" message={error} />}

                <p className="text-sm text-muted">
                  Please provide a reason for rejecting this property. The owner
                  will be notified and can resubmit after making corrections.
                </p>

                <div>
                  <label className="block text-sm font-medium text-text mb-1.5">
                    Rejection Reason <span className="text-red-500">*</span>
                  </label>
                  <textarea
                    value={rejectionReason}
                    onChange={(e) => setRejectionReason(e.target.value)}
                    rows={4}
                    placeholder="e.g., Incomplete documentation. Please provide title deed."
                    className="w-full px-4 py-3 rounded-lg border border-slate-300 hover:border-slate-400 transition-all duration-200 text-text placeholder:text-muted focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary resize-none"
                  />
                </div>
              </div>

              {/* Modal Footer */}
              <div className="flex justify-end gap-3 mt-6">
                <Button
                  variant="secondary"
                  onClick={() => setShowRejectModal(false)}
                >
                  Cancel
                </Button>
                <Button
                  variant="danger"
                  onClick={handleReject}
                  loading={isLoading}
                  disabled={isLoading || !rejectionReason.trim()}
                >
                  Reject Property
                </Button>
              </div>
            </div>
          </div>
        </div>
      )}
    </>
  );
};

export default ApprovalActions;
