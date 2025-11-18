import { home } from '@/routes';
import { Link } from '@inertiajs/react';
import { type PropsWithChildren } from 'react';

interface AuthLayoutProps {
    name?: string;
    title?: string;
    description?: string;
}

export default function AuthSimpleLayout({
    children,
    title,
    description,
}: PropsWithChildren<AuthLayoutProps>) {
    return (
        <div className="flex min-h-svh flex-col items-center justify-center gap-6 bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 dark:from-gray-900 dark:via-slate-900 dark:to-gray-900 p-6 md:p-10">
            <div className="w-full max-w-md">
                <div className="flex flex-col gap-8 bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl p-8 rounded-2xl shadow-2xl border border-gray-200/50 dark:border-gray-700/50">
                    <div className="flex flex-col items-center gap-6">
                        <Link
                            href={home()}
                            className="flex flex-col items-center gap-3 font-medium transition-transform hover:scale-105"
                        >
                            <div className="mb-2 flex items-center justify-center ">
                                <img 
                                    src="/logo.png" 
                                    alt="Logo" 
                                    className="h-30 w-50 object-contain"
                                />
                            </div>
                            {/* <span className="text-2xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">
                                Ubiqent
                            </span> */}
                        </Link>

                        <div className="space-y-2 text-center">
                            <h1 className="text-2xl font-semibold text-gray-900 dark:text-white">
                                {title}
                            </h1>
                            <p className="text-center text-sm text-gray-600 dark:text-gray-400">
                                {description}
                            </p>
                        </div>
                    </div>
                    {children}
                </div>
            </div>
        </div>
    );
}
