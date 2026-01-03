import { XMarkIcon } from '@heroicons/react/24/outline';
import { CheckCircleIcon, ExclamationTriangleIcon, XCircleIcon, InformationCircleIcon } from '@heroicons/react/24/solid';

const Alert = ({ type = 'info', message, onClose, className = '' }) => {
  const styles = {
    success: {
      bg: 'bg-green-50',
      border: 'border-l-4 border-success',
      text: 'text-green-800',
      icon: CheckCircleIcon,
      iconColor: 'text-success',
    },
    error: {
      bg: 'bg-red-50',
      border: 'border-l-4 border-error',
      text: 'text-red-800',
      icon: XCircleIcon,
      iconColor: 'text-error',
    },
    warning: {
      bg: 'bg-amber-50',
      border: 'border-l-4 border-warning',
      text: 'text-amber-800',
      icon: ExclamationTriangleIcon,
      iconColor: 'text-warning',
    },
    info: {
      bg: 'bg-blue-50',
      border: 'border-l-4 border-primary',
      text: 'text-blue-800',
      icon: InformationCircleIcon,
      iconColor: 'text-primary',
    },
  };

  const currentStyle = styles[type];
  const Icon = currentStyle.icon;

  return (
    <div
      className={`
        ${currentStyle.bg} ${currentStyle.border} ${currentStyle.text}
        p-4 rounded-r-lg flex items-start gap-3
        ${className}
      `}
      role="alert"
    >
      <Icon className={`h-5 w-5 ${currentStyle.iconColor} flex-shrink-0 mt-0.5`} />
      <p className="flex-1 text-sm">{message}</p>
      {onClose && (
        <button
          onClick={onClose}
          className="flex-shrink-0 hover:opacity-70 transition-opacity"
        >
          <XMarkIcon className="h-5 w-5" />
        </button>
      )}
    </div>
  );
};

export default Alert;
