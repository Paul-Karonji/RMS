import { useState, useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { toast } from 'react-toastify';
import { ArrowLeftIcon } from '@heroicons/react/24/outline';
import { Card, LoadingSpinner, Alert } from '../../components/common';
import { UnitForm, UnitStatusBadge } from '../../components/properties';
import propertyService from '../../services/propertyService';

const EditUnit = () => {
  const navigate = useNavigate();
  const { id } = useParams();

  const [unit, setUnit] = useState(null);
  const [loading, setLoading] = useState(true);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [error, setError] = useState(null);
  const [fetchError, setFetchError] = useState(null);

  useEffect(() => {
    const fetchUnit = async () => {
      try {
        const response = await propertyService.getUnit(id);
        if (response.success) {
          setUnit(response.data);
        } else {
          setFetchError(response.message || 'Failed to fetch unit');
        }
      } catch (err) {
        setFetchError(err.message || 'Failed to fetch unit');
      } finally {
        setLoading(false);
      }
    };

    fetchUnit();
  }, [id]);

  const handleSubmit = async (data) => {
    setIsSubmitting(true);
    setError(null);

    try {
      const response = await propertyService.updateUnit(id, data);

      if (response.success) {
        toast.success('Unit updated successfully');
        // Navigate back to property details
        if (unit?.property_id) {
          navigate(`/company/properties/${unit.property_id}`);
        } else {
          navigate('/company/properties');
        }
      } else {
        setError(response.message || 'Failed to update unit');
      }
    } catch (err) {
      setError(err.message || 'Failed to update unit');
      if (err.errors) {
        const errorMessages = Object.values(err.errors).flat().join(', ');
        setError(errorMessages);
      }
    } finally {
      setIsSubmitting(false);
    }
  };

  const isOccupied = unit?.status === 'occupied';

  if (loading) {
    return (
      <div className="flex justify-center py-12">
        <LoadingSpinner size="lg" />
      </div>
    );
  }

  if (fetchError) {
    return (
      <div className="max-w-3xl mx-auto space-y-6">
        <button
          onClick={() => navigate('/company/properties')}
          className="flex items-center gap-2 text-muted hover:text-text transition-colors"
        >
          <ArrowLeftIcon className="w-4 h-4" />
          <span>Back to Properties</span>
        </button>
        <Alert type="error" message={fetchError} />
      </div>
    );
  }

  const backPath = unit?.property_id 
    ? `/company/properties/${unit.property_id}` 
    : '/company/properties';

  return (
    <div className="max-w-3xl mx-auto space-y-6">
      {/* Back Button */}
      <button
        onClick={() => navigate(backPath)}
        className="flex items-center gap-2 text-muted hover:text-text transition-colors"
      >
        <ArrowLeftIcon className="w-4 h-4" />
        <span>Back to Property</span>
      </button>

      {/* Page Header */}
      <div className="flex items-start justify-between">
        <div>
          <h1 className="text-2xl font-bold text-text">Edit Unit</h1>
          <p className="text-muted mt-1">
            Unit {unit?.unit_number}
          </p>
        </div>
        <UnitStatusBadge status={unit?.status} />
      </div>

      {/* Form Card */}
      <Card padding="lg">
        <UnitForm
          initialData={unit}
          onSubmit={handleSubmit}
          isLoading={isSubmitting}
          error={error}
          submitLabel="Update Unit"
          isEdit
          isOccupied={isOccupied}
        />
      </Card>
    </div>
  );
};

export default EditUnit;
