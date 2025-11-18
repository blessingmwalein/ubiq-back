import { PropsWithChildren, ReactNode } from 'react';
import { Head, Link, usePage } from '@inertiajs/react';
import {
    LayoutDashboard,
    Users,
    Film,
    Package,
    CreditCard,
    BarChart3,
    Settings,
    Menu,
    Bell,
    Search,
    LogOut,
    Tag,
    Building2,
    Tv,
} from 'lucide-react';
import { User } from '@/types';

interface AdminLayoutProps extends PropsWithChildren {
    title?: string;
    header?: ReactNode;
}

export default function AdminLayout({ title, header, children }: AdminLayoutProps) {
    const { auth } = usePage<{ auth: { user: User } }>().props;

    const navigation = [
        { name: 'Dashboard', href: '/admin', icon: LayoutDashboard },
        { name: 'Content', href: '/admin/content', icon: Film },
        { name: 'Shows', href: '/admin/shows', icon: Tv },
        { name: 'Categories', href: '/admin/categories', icon: Tag },
        { name: 'Providers', href: '/admin/providers', icon: Building2 },
        { name: 'Users', href: '/admin/users', icon: Users },
        { name: 'Packages', href: '/admin/packages', icon: Package },
        { name: 'Subscriptions', href: '/admin/subscriptions', icon: CreditCard },
        { name: 'Analytics', href: '/admin/analytics', icon: BarChart3 },
        { name: 'Settings', href: '/admin/settings', icon: Settings },
    ];

    return (
        <>
            <Head title={title} />
            <div className="min-h-screen bg-gradient-to-br from-gray-50 to-blue-50 dark:from-gray-900 dark:to-slate-900">
                {/* Sidebar */}
                <aside className="fixed inset-y-0 left-0 z-50 w-64 bg-white/95 dark:bg-gray-800/95 backdrop-blur-sm border-r border-blue-100 dark:border-gray-700 shadow-xl">
                    {/* Logo */}
                    <div className="flex items-center justify-center h-16 border-b border-blue-100 dark:border-gray-700 bg-gradient-to-r from-blue-500/5 to-indigo-500/5">
                        <Link href="/" className="flex items-center gap-3 font-bold group">
                            <div className="flex items-center justify-center  transition-transform group-hover:scale-110">
                                <img 
                                    src="/logo.png" 
                                    alt="Ubiqent Logo" 
                                    className="h-30 w-50 object-contain"
                                />
                            </div>
                            {/* <span className="text-xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">
                                Ubiqent
                            </span> */}
                        </Link>
                    </div>

                    {/* Navigation */}
                    <nav className="p-4 space-y-1">
                        {navigation.map((item) => {
                            const Icon = item.icon;
                            const isActive = window.location.pathname === item.href || 
                                            window.location.pathname.startsWith(item.href + '/');
                            
                            return (
                                <Link
                                    key={item.name}
                                    href={item.href}
                                    className={`flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-all duration-200 ${
                                        isActive
                                            ? 'bg-gradient-to-r from-blue-50 to-indigo-50 text-blue-600 shadow-md dark:from-blue-900/30 dark:to-indigo-900/30 dark:text-blue-400 border-l-4 border-blue-600'
                                            : 'text-gray-700 hover:bg-gradient-to-r hover:from-gray-50 hover:to-blue-50 dark:text-gray-300 dark:hover:bg-gray-700/50'
                                    }`}
                                >
                                    <Icon className="w-5 h-5 mr-3" />
                                    {item.name}
                                </Link>
                            );
                        })}
                    </nav>
                </aside>

                {/* Main Content */}
                <div className="pl-64">
                    {/* Top Header */}
                    <header className="sticky top-0 z-40 bg-white/95 dark:bg-gray-800/95 backdrop-blur-sm border-b border-blue-100 dark:border-gray-700 shadow-sm">
                        <div className="flex items-center justify-between h-16 px-6">
                            <div className="flex items-center flex-1">
                                <button className="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 lg:hidden">
                                    <Menu className="w-6 h-6" />
                                </button>

                                {/* Search */}
                                <div className="relative ml-4 w-96">
                                    <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-blue-400" />
                                    <input
                                        type="text"
                                        placeholder="Search..."
                                        className="w-full pl-10 pr-4 py-2 border border-blue-200 dark:border-gray-600 rounded-lg bg-gradient-to-r from-blue-50/50 to-indigo-50/50 dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                    />
                                </div>
                            </div>

                            <div className="flex items-center space-x-4">
                                {/* Notifications */}
                                <button className="relative p-2 rounded-lg hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 dark:hover:bg-gray-700 transition-all">
                                    <Bell className="w-6 h-6 text-gray-700 dark:text-gray-300" />
                                    <span className="absolute top-1 right-1 w-2 h-2 bg-gradient-to-r from-red-500 to-pink-500 rounded-full animate-pulse"></span>
                                </button>

                                {/* User Menu */}
                                <div className="flex items-center space-x-3 bg-gradient-to-r from-blue-50/50 to-indigo-50/50 dark:from-gray-700/50 dark:to-gray-600/50 px-4 py-2 rounded-lg">
                                    <div className="text-right">
                                        <div className="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {auth.user.name}
                                        </div>
                                        <div className="text-xs text-blue-600 dark:text-blue-400">
                                            {auth.user.email}
                                        </div>
                                    </div>
                                    <Link
                                        href="/logout"
                                        method="post"
                                        as="button"
                                        className="p-2 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/30 text-gray-700 hover:text-red-600 dark:text-gray-300 dark:hover:text-red-400 transition-all"
                                        title="Logout"
                                    >
                                        <LogOut className="w-5 h-5" />
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </header>

                    {/* Page Header */}
                    {header && (
                        <div className="bg-gradient-to-r from-white to-blue-50/30 dark:from-gray-800 dark:to-slate-800 border-b border-blue-100 dark:border-gray-700">
                            <div className="px-6 py-6">{header}</div>
                        </div>
                    )}

                    {/* Page Content */}
                    <main className="p-6">
                        <div className="max-w-7xl mx-auto">
                            {children}
                        </div>
                    </main>
                </div>
            </div>
        </>
    );
}
