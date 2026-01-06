import { useState } from 'react';
import { Button, Alert } from '../common';
import {
  UserPlusIcon,
  XMarkIcon,
  UserCircleIcon,
} from '@heroicons/react/24/outline';

const ManagerAssignment = ({
  propertyId,
  currentManager = null,
  staffList = [],
  onAssign,
  isLoading = false,
}) => {
  const [showModal, setShowModal] = useState(false);
  const [selectedManagerId, setSelectedManagerId] = useState('');
  const [error, setError] = useState(null);

  const handleAssign = async () => {
    if (!selectedManagerId) {
      setError('Please select a manager');
      return;
    }

    setError(null);
    try {
      await onAssign(propertyId, selectedManagerId);
      setShowModal(false);
      setSelectedManagerId('');
    } catch (err) {
      setError(err.message || 'Failed to assign manager');
    }
  };

  return (
    <>
      {/* Current Manager Display */}
      <div className="flex items-center gap-3">
        {currentManager ? (
          <div className="flex items-center gap-2 px-3 py-2 bg-slate-50 rounded-lg">
            <UserCircleIcon className="w-5 h-5 text-muted" />
            <span className="text-sm text-text">{currentManager.name}</span>
            <Button
              size="sm"
              variant="ghost"
              onClick={() => setShowModal(true)}
              className="ml-2"
            >
              Change
            </Button>
          </div>
        ) : (
          <Button
            variant="secondary"
            onClick={() => setShowModal(true)}
            disabled={isLoading}
          >
            <UserPlusIcon className="w-5 h-5 mr-2" />
            Assign Manager
          </Button>
        )}
      </div>

      {/* Assignment Modal */}
      {showModal && (
        <div className="fixed inset-0 z-50 overflow-y-auto">
          <div
            className="fixed inset-0 bg-black bg-opacity-50 transition-opacity"
            onClick={() => setShowModal(false)}
          />

          <div className="flex min-h-full items-center justify-center p-4">
            <div className="relative bg-surface rounded-lg shadow-xl max-w-md w-full p-6">
              {/* Modal Header */}
              <div className="flex items-center justify-between mb-4">
                <h3 className="text-lg font-semibold text-text">
                  Assign Property Manager
                </h3>
                <button
                  onClick={() => setShowModal(false)}
                  className="p-1 rounded hover:bg-slate-100"
                >
                  <XMarkIcon className="w-5 h-5 text-muted" />
                </button>
              </div>

              {/* Modal Body */}
              <div className="space-y-4">
                {error && <Alert type="error" message={error} />}

                <p className="text-sm text-muted">
                  Select a staff member to manage this property. They will be
                  able to add units, manage tenants, and handle maintenance.
                </p>

                {staffList.length > 0 ? (
                  <div>
                    <label className="block text-sm font-medium text-text mb-1.5">
                      Select Manager <span className="text-red-500">*</span>
                    </label>
                    <select
                      value={selectedManagerId}
                      onChange={(e) => setSelectedManagerId(e.target.value)}
                      className="w-full px-4 py-3 rounded-lg border border-slate-300 hover:border-slate-400 transition-all duration-200 text-text bg-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                    >
                      <option value="">-- Select a staff member --</option>
                      {staffList.map((staff) => (
                        <option key={staff.id} value={staff.id}>
                          {staff.name} ({staff.email})
                        </option>
                      ))}
                    </select>
                  </div>
                ) : (
                  <div className="text-center py-4">
                    <p className="text-muted">No staff members available.</p>
                    <p className="text-sm text-muted mt-1">
                      Add staff members to your company first.
                    </p>
                  </div>
                )}
              </div>

              {/* Modal Footer */}
              <div className="flex justify-end gap-3 mt-6">
                <Button
                  variant="secondary"
                  onClick={() => setShowModal(false)}
                >
                  Cancel
                </Button>
                <Button
                  onClick={handleAssign}
                  loading={isLoading}
                  disabled={isLoading || !selectedManagerId || staffList.length === 0}
                >
                  Assign Manager
                </Button>
              </div>
            </div>
          </div>
        </div>
      )}
    </>
  );
};

export default ManagerAssignment;
