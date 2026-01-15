import React, { useState, useRef } from 'react';
import { Upload, X, File, Image } from 'lucide-react';
import './FileUpload.css';

const FileUpload = ({
    onFileSelect,
    accept = 'image/*',
    maxSize = 5 * 1024 * 1024, // 5MB default
    multiple = false,
    label = 'Upload File'
}) => {
    const [files, setFiles] = useState([]);
    const [error, setError] = useState('');
    const [isDragging, setIsDragging] = useState(false);
    const fileInputRef = useRef(null);

    const validateFile = (file) => {
        if (file.size > maxSize) {
            const maxSizeMB = maxSize / (1024 * 1024);
            throw new Error(`File size must not exceed ${maxSizeMB}MB`);
        }

        if (accept && accept !== '*') {
            const acceptedTypes = accept.split(',').map(t => t.trim());
            const fileType = file.type;
            const fileExtension = '.' + file.name.split('.').pop();

            const isAccepted = acceptedTypes.some(type => {
                if (type.endsWith('/*')) {
                    return fileType.startsWith(type.replace('/*', ''));
                }
                return type === fileType || type === fileExtension;
            });

            if (!isAccepted) {
                throw new Error(`File type not accepted. Allowed: ${accept}`);
            }
        }
    };

    const handleFiles = (fileList) => {
        setError('');
        const newFiles = Array.from(fileList);

        try {
            newFiles.forEach(validateFile);

            if (multiple) {
                setFiles([...files, ...newFiles]);
                onFileSelect([...files, ...newFiles]);
            } else {
                setFiles(newFiles);
                onFileSelect(newFiles[0]);
            }
        } catch (err) {
            setError(err.message);
        }
    };

    const handleFileInput = (e) => {
        if (e.target.files && e.target.files.length > 0) {
            handleFiles(e.target.files);
        }
    };

    const handleDrop = (e) => {
        e.preventDefault();
        setIsDragging(false);

        if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
            handleFiles(e.dataTransfer.files);
        }
    };

    const handleDragOver = (e) => {
        e.preventDefault();
        setIsDragging(true);
    };

    const handleDragLeave = () => {
        setIsDragging(false);
    };

    const removeFile = (index) => {
        const newFiles = files.filter((_, i) => i !== index);
        setFiles(newFiles);
        onFileSelect(multiple ? newFiles : null);
    };

    const getFileIcon = (file) => {
        if (file.type.startsWith('image/')) {
            return <Image size={20} />;
        }
        return <File size={20} />;
    };

    const formatFileSize = (bytes) => {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    };

    return (
        <div className="file-upload-container">
            <div
                className={`file-upload-dropzone ${isDragging ? 'dragging' : ''}`}
                onDrop={handleDrop}
                onDragOver={handleDragOver}
                onDragLeave={handleDragLeave}
                onClick={() => fileInputRef.current?.click()}
            >
                <Upload size={32} />
                <p className="upload-text">{label}</p>
                <p className="upload-hint">
                    Drag and drop or click to browse
                </p>
                <input
                    ref={fileInputRef}
                    type="file"
                    accept={accept}
                    multiple={multiple}
                    onChange={handleFileInput}
                    style={{ display: 'none' }}
                />
            </div>

            {error && (
                <div className="upload-error">
                    {error}
                </div>
            )}

            {files.length > 0 && (
                <div className="uploaded-files">
                    {files.map((file, index) => (
                        <div key={index} className="uploaded-file-item">
                            <div className="file-icon">
                                {getFileIcon(file)}
                            </div>
                            <div className="file-info">
                                <span className="file-name">{file.name}</span>
                                <span className="file-size">{formatFileSize(file.size)}</span>
                            </div>
                            <button
                                className="remove-file-btn"
                                onClick={(e) => {
                                    e.stopPropagation();
                                    removeFile(index);
                                }}
                            >
                                <X size={16} />
                            </button>
                        </div>
                    ))}
                </div>
            )}
        </div>
    );
};

export default FileUpload;
