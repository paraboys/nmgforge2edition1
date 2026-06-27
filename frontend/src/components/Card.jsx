import React from 'react';

export default function Card({ children, className = '' }) {
  return (
    <div className={`bg-white shadow sm:rounded-lg ${className}`}>
      {children}
    </div>
  );
}

export function CardHeader({ children, className = '' }) {
  return (
    <div className={`px-4 py-5 sm:px-6 ${className}`}>
      {children}
    </div>
  );
}

export function CardBody({ children, className = '' }) {
  return (
    <div className={`border-t border-gray-200 px-4 py-5 sm:px-6 ${className}`}>
      {children}
    </div>
  );
}
