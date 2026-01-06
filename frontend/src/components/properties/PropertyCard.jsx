import { useNavigate } from 'react-router-dom';
import {
  BuildingOfficeIcon,
  MapPinIcon,
  HomeModernIcon,
  EllipsisVerticalIcon,
} from '@heroicons/react/24/outline';
import { useState, useRef, useEffect } from 'react';
import PropertyStatusBadge from './PropertyStatusBadge';

const PropertyCard = ({ property, onEdit, onDelete, showActions = true }) => {
  const navigate = useNavigate();
  const [menuOpen, setMenuOpen] = useState(false);
  const menuRef = useRef(null);

  useEffect(() => {
    const handleClickOutside = (event) => {
      if (menuRef.current && !menuRef.current.contains(event.target)) {
        setMenuOpen(false);
      }
    };
    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  const handleCardClick = () => {
    navigate(`/company/properties/${property.id}`);
  };

  const handleMenuClick = (e) => {
    e.stopPropagation();
    setMenuOpen(!menuOpen);
  };

  const handleEdit = (e) => {
    e.stopPropagation();
    setMenuOpen(false);
    if (onEdit) {
      onEdit(property);
    } else {
      navigate(`/company/properties/${property.id}/edit`);
    }
  };

  const handleDelete = (e) => {
    e.stopPropagation();
    setMenuOpen(false);
    if (onDelete) {
      onDelete(property);
    }
  };

  const propertyTypeIcons = {
    apartment: '🏢',
    house: '🏠',
    office: '🏛️',
    shop: '🏪',
    warehouse: '🏭',
  };

  return (
    <div
      onClick={handleCardClick}
      className="bg-surface rounded-lg border border-slate-200 shadow-sm hover:shadow-md transition-shadow cursor-pointer overflow-hidden"
    >
      {/* Card Header */}
      <div className="p-4 border-b border-slate-100">
        <div className="flex items-start justify-between">
          <div className="flex items-center gap-3">
            <div className="w-10 h-10 bg-primary/10 rounded-lg flex items-center justify-center">
              <span className="text-xl">
                {propertyTypeIcons[property.property_type] || '🏠'}
              </span>
            </div>
            <div>
              <h3 className="font-semibold text-text line-clamp-1">
                {property.property_name || property.name}
              </h3>
              <p className="text-sm text-muted capitalize">
                {property.property_type}
              </p>
            </div>
          </div>

          {showActions && (
            <div className="relative" ref={menuRef}>
              <button
                onClick={handleMenuClick}
                className="p-1 rounded hover:bg-slate-100 transition-colors"
              >
                <EllipsisVerticalIcon className="w-5 h-5 text-muted" />
              </button>

              {menuOpen && (
                <div className="absolute right-0 mt-1 w-36 bg-surface rounded-lg shadow-lg border border-slate-200 py-1 z-10">
                  <button
                    onClick={handleEdit}
                    className="w-full px-4 py-2 text-left text-sm text-text hover:bg-slate-50"
                  >
                    Edit
                  </button>
                  <button
                    onClick={handleDelete}
                    className="w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50"
                  >
                    Delete
                  </button>
                </div>
              )}
            </div>
          )}
        </div>
      </div>

      {/* Card Body */}
      <div className="p-4 space-y-3">
        {/* Location */}
        <div className="flex items-center gap-2 text-sm text-muted">
          <MapPinIcon className="w-4 h-4 flex-shrink-0" />
          <span className="line-clamp-1">
            {property.city}
            {property.county && `, ${property.county}`}
          </span>
        </div>

        {/* Address */}
        {property.address && (
          <p className="text-sm text-muted line-clamp-2">{property.address}</p>
        )}

        {/* Stats */}
        <div className="flex items-center justify-between pt-2">
          <div className="flex items-center gap-1.5 text-sm text-muted">
            <HomeModernIcon className="w-4 h-4" />
            <span>{property.total_units || 0} units</span>
          </div>
          <PropertyStatusBadge status={property.approval_status || property.status} />
        </div>
      </div>

      {/* Card Footer - Fee Info */}
      <div className="px-4 py-3 bg-slate-50 border-t border-slate-100">
        <div className="flex items-center justify-between text-sm">
          <span className="text-muted">Platform Fee</span>
          <span className="font-medium text-text">
            {property.fee_type === 'percentage'
              ? `${property.fee_value}%`
              : `KES ${Number(property.fee_value).toLocaleString()}`}
          </span>
        </div>
      </div>
    </div>
  );
};

export default PropertyCard;
