import { useState, useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { toast } from 'react-toastify';
import {
  ArrowLeftIcon,
  PencilIcon,
  TrashIcon,
  PlusIcon,
  MapPinIcon,
  BuildingOfficeIcon,
  HomeModernIcon,
  ArrowPathIcon,
} from '@heroicons/react/24/outline';
import { Card, Button, LoadingSpinner, Alert } from '../../components/common';
import {
  PropertyStatusBadge,
  UnitCard,
  ApprovalActions,
  ManagerAssignment,
} from '../../components/properties';
import propertyService from '../../services/propertyService';
import { useAuth } from '../../hooks/useAuth';

const PropertyDetails = () => {
  const navigate = useNavigate();
  const { id } = useParams();
  const { user } = useAuth();

  const [property, setProperty] = useState(null);
  const [units, setUnits] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [actionLoading, setActionLoading] = useState(false);

  const fetchProperty = async () => {
    try {
      const response = await propertyService.getProperty(id);
      if (response.success) {
        setProperty(response.data);
        setUnits(response.data.units || []);
      } else {
        setError(response.message || 'Failed to fetch property');
      }
    } catch (err) {
      setError(err.message || 'Failed to fetch property');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchProperty();
  }, [id]);

  const handleDelete = async () => {
    if (!window.confirm(`Are you sure you want to delete "${property?.property_name || property?.name}"?`)) {
      return;
    }

    setActionLoading(true);
    try {
      const response = await propertyService.deleteProperty(id);
      if (response.success) {
        toast.success('Property deleted successfully');
        navigate('/company/properties');
      } else {
        toast.error(response.message || 'Failed to delete property');
      }
    } catch (err) {
      toast.error(err.message || 'Failed to delete property');
    } finally {
      setActionLoading(false);
    }
  };

  const handleApprove = async (propertyId) => {
    setActionLoading(true);
    try {
      const response = await propertyService.approveProperty(propertyId);
      if (response.success) {
        toast.success('Property approved successfully');
        fetchProperty();
      } else {
        toast.error(response.message || 'Failed to approve property');
      }
    } catch (err) {
      toast.error(err.message || 'Failed to approve property');
    } finally {
      setActionLoading(false);
    }
  };

  const handleReject = async (propertyId, reason) => {
    setActionLoading(true);
    try {
      const response = await propertyService.rejectProperty(propertyId, reason);
      if (response.success) {
        toast.success('Property rejected');
        fetchProperty();
      } else {
        toast.error(response.message || 'Failed to reject property');
      }
    } catch (err) {
      toast.error(err.message || 'Failed to reject property');
    } finally {
      setActionLoading(false);
    }
  };

  const handleResubmit = async () => {
    setActionLoading(true);
    try {
      const response = await propertyService.resubmitProperty(id);
      if (response.success) {
        toast.success('Property resubmitted for approval');
        fetchProperty();
      } else {
        toast.error(response.message || 'Failed to resubmit property');
      }
    } catch (err) {
      toast.error(err.message || 'Failed to resubmit property');
    } finally {
      setActionLoading(false);
    }
  };

  const handleAssignManager = async (propertyId, managerId) => {
    setActionLoading(true);
    try {
      const response = await propertyService.assignManager(propertyId, managerId);
      if (response.success) {
        toast.success('Manager assigned successfully');
        fetchProperty();
      } else {
        toast.error(response.message || 'Failed to assign manager');
      }
    } catch (err) {
      toast.error(err.message || 'Failed to assign manager');
    } finally {
      setActionLoading(false);
    }
  };

  const handleDeleteUnit = async (unit) => {
    if (!window.confirm(`Are you sure you want to delete Unit ${unit.unit_number}?`)) {
      return;
    }

    try {
      const response = await propertyService.deleteUnit(unit.id);
      if (response.success) {
        toast.success('Unit deleted successfully');
        fetchProperty();
      } else {
        toast.error(response.message || 'Failed to delete unit');
      }
    } catch (err) {
      toast.error(err.message || 'Failed to delete unit');
    }
  };

  const isAdmin = user?.role === 'company_admin';
  const isOwner = user?.role === 'property_owner';
  const status = property?.approval_status || property?.status;
  const canEdit = status === 'pending_approval' || status === 'rejected';
  const canAddUnits = status === 'approved';
  const canResubmit = status === 'rejected' && isOwner;

  if (loading) {
    return (
      <div className="flex justify-center py-12">
        <LoadingSpinner size="lg" />
      </div>
    );
  }

  if (error) {
    return (
      <div className="space-y-6">
        <button
          onClick={() => navigate('/company/properties')}
          className="flex items-center gap-2 text-muted hover:text-text transition-colors"
        >
          <ArrowLeftIcon className="w-4 h-4" />
          <span>Back to Properties</span>
        </button>
        <Alert type="error" message={error} />
      </div>
    );
  }

  return (
    <div className="space-y-6">
      {/* Back Button */}
      <button
        onClick={() => navigate('/company/properties')}
        className="flex items-center gap-2 text-muted hover:text-text transition-colors"
      >
        <ArrowLeftIcon className="w-4 h-4" />
        <span>Back to Properties</span>
      </button>

      {/* Header */}
      <div className="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
        <div className="flex items-start gap-4">
          <div className="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center flex-shrink-0">
            <BuildingOfficeIcon className="w-6 h-6 text-primary" />
          </div>
          <div>
            <div className="flex items-center gap-3">
              <h1 className="text-2xl font-bold text-text">
                {property?.property_name || property?.name}
              </h1>
              <PropertyStatusBadge status={status} />
            </div>
            <div className="flex items-center gap-2 mt-1 text-muted">
              <span className="capitalize">{property?.property_type}</span>
              <span>•</span>
              <MapPinIcon className="w-4 h-4" />
              <span>{property?.city}, {property?.county}</span>
            </div>
          </div>
        </div>

        {/* Actions */}
        <div className="flex flex-wrap items-center gap-3">
          {canResubmit && (
            <Button
              variant="secondary"
              onClick={handleResubmit}
              loading={actionLoading}
            >
              <ArrowPathIcon className="w-5 h-5 mr-2" />
              Resubmit
            </Button>
          )}
          {canEdit && (
            <Button
              variant="secondary"
              onClick={() => navigate(`/company/properties/${id}/edit`)}
            >
              <PencilIcon className="w-5 h-5 mr-2" />
              Edit
            </Button>
          )}
          {isAdmin && (
            <Button
              variant="danger"
              onClick={handleDelete}
              loading={actionLoading}
            >
              <TrashIcon className="w-5 h-5 mr-2" />
              Delete
            </Button>
          )}
        </div>
      </div>

      {/* Rejection Notice */}
      {status === 'rejected' && property?.approval_notes && (
        <Alert
          type="error"
          message={`Rejection Reason: ${property.approval_notes}`}
        />
      )}

      {/* Admin Approval Actions */}
      {isAdmin && status === 'pending_approval' && (
        <Card padding="md">
          <div className="flex items-center justify-between">
            <div>
              <h3 className="font-medium text-text">Approval Required</h3>
              <p className="text-sm text-muted">
                Review this property and approve or reject it.
              </p>
            </div>
            <ApprovalActions
              propertyId={id}
              currentStatus={status}
              onApprove={handleApprove}
              onReject={handleReject}
              isLoading={actionLoading}
            />
          </div>
        </Card>
      )}

      {/* Property Details Grid */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Main Info */}
        <Card padding="md" className="lg:col-span-2">
          <h2 className="text-lg font-semibold text-text mb-4">Property Details</h2>
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <p className="text-sm text-muted">Address</p>
              <p className="font-medium text-text">{property?.address || 'N/A'}</p>
            </div>
            <div>
              <p className="text-sm text-muted">City</p>
              <p className="font-medium text-text">{property?.city}</p>
            </div>
            <div>
              <p className="text-sm text-muted">County</p>
              <p className="font-medium text-text">{property?.county}</p>
            </div>
            <div>
              <p className="text-sm text-muted">Total Units</p>
              <p className="font-medium text-text">{property?.total_units || 0}</p>
            </div>
          </div>
          {property?.description && (
            <div className="mt-4 pt-4 border-t border-slate-200">
              <p className="text-sm text-muted mb-1">Description</p>
              <p className="text-text">{property.description}</p>
            </div>
          )}
        </Card>

        {/* Fee Structure & Manager */}
        <div className="space-y-6">
          <Card padding="md">
            <h2 className="text-lg font-semibold text-text mb-4">Fee Structure</h2>
            <div className="space-y-3">
              <div>
                <p className="text-sm text-muted">Fee Type</p>
                <p className="font-medium text-text capitalize">
                  {property?.fee_type === 'percentage' ? 'Percentage' : 'Flat Amount'}
                </p>
              </div>
              <div>
                <p className="text-sm text-muted">Fee Value</p>
                <p className="font-medium text-text">
                  {property?.fee_type === 'percentage'
                    ? `${property?.fee_value}%`
                    : `KES ${Number(property?.fee_value).toLocaleString()}`}
                </p>
              </div>
            </div>
          </Card>

          {isAdmin && status === 'approved' && (
            <Card padding="md">
              <h2 className="text-lg font-semibold text-text mb-4">Property Manager</h2>
              <ManagerAssignment
                propertyId={id}
                currentManager={property?.manager}
                staffList={[]} // TODO: Fetch staff list
                onAssign={handleAssignManager}
                isLoading={actionLoading}
              />
            </Card>
          )}
        </div>
      </div>

      {/* Units Section */}
      <Card padding="md">
        <div className="flex items-center justify-between mb-6">
          <div className="flex items-center gap-3">
            <HomeModernIcon className="w-6 h-6 text-primary" />
            <h2 className="text-lg font-semibold text-text">
              Units ({units.length})
            </h2>
          </div>
          {canAddUnits && (
            <Button
              onClick={() => navigate(`/company/properties/${id}/units/create`)}
            >
              <PlusIcon className="w-5 h-5 mr-2" />
              Add Unit
            </Button>
          )}
        </div>

        {!canAddUnits && status !== 'approved' && (
          <Alert
            type="info"
            message="Units can only be added after the property is approved."
          />
        )}

        {units.length === 0 ? (
          <div className="text-center py-8">
            <HomeModernIcon className="w-12 h-12 text-muted mx-auto mb-3" />
            <p className="text-muted">No units added yet</p>
            {canAddUnits && (
              <Button
                variant="secondary"
                className="mt-4"
                onClick={() => navigate(`/company/properties/${id}/units/create`)}
              >
                <PlusIcon className="w-5 h-5 mr-2" />
                Add First Unit
              </Button>
            )}
          </div>
        ) : (
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            {units.map((unit) => (
              <UnitCard
                key={unit.id}
                unit={unit}
                onDelete={handleDeleteUnit}
              />
            ))}
          </div>
        )}
      </Card>
    </div>
  );
};

export default PropertyDetails;
