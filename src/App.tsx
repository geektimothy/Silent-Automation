import React, { useState, useEffect, useMemo } from 'react';
import { 
  BarChart3, 
  Zap, 
  Users, 
  MousePointer2, 
  LayoutDashboard, 
  Settings, 
  Bell,
  Search,
  ChevronRight,
  Plus,
  CheckCircle2,
  XCircle,
  ExternalLink,
  Code2,
  FileJson,
  Database
} from 'lucide-react';
import { motion, AnimatePresence } from 'motion/react';

// --- Mock Data & Types ---

interface Event {
  id: string;
  sessionId: string;
  pageUrl: string;
  eventType: 'visit' | 'time_spent';
  value: number;
  createdAt: Date;
}

interface Pattern {
  id: string;
  type: 'high_intent' | 'engaged';
  pageUrl: string;
  condition: string;
  message: string;
}

interface ActiveRule {
  id: string;
  pageUrl: string;
  type: string;
  message: string;
}

// --- Components ---

const SidebarItem = ({ icon: Icon, label, active = false, onClick }: any) => (
  <div 
    onClick={onClick}
    className={`flex items-center gap-3 px-4 py-2.5 cursor-pointer transition-colors ${
      active ? 'bg-[#2271b1] text-white' : 'text-[#c3c4c7] hover:bg-[#32373c] hover:text-[#72aee6]'
    }`}
  >
    <Icon size={18} />
    <span className="text-[14px] font-medium">{label}</span>
  </div>
);

const StatCard = ({ title, value, icon: Icon }: any) => (
  <div className="bg-white p-5 border border-[#ccd0d4] shadow-sm">
    <div className="flex justify-between items-start mb-2">
      <h3 className="text-[#646970] text-[14px] font-normal uppercase tracking-wider">{title}</h3>
      <Icon size={18} className="text-[#2271b1]" />
    </div>
    <p className="text-[28px] font-semibold text-[#1d2327]">{value}</p>
  </div>
);

export default function App() {
  const [events, setEvents] = useState<Event[]>([]);
  const [activeRules, setActiveRules] = useState<ActiveRule[]>([]);
  const [currentTab, setCurrentTab] = useState('dashboard');
  const [showPopup, setShowPopup] = useState(false);
  const [popupMessage, setPopupMessage] = useState('');

  // Simulate initial data
  useEffect(() => {
    const initialEvents: Event[] = [
      { id: '1', sessionId: 's1', pageUrl: '/pricing', eventType: 'visit', value: 1, createdAt: new Date() },
      { id: '2', sessionId: 's1', pageUrl: '/pricing', eventType: 'visit', value: 1, createdAt: new Date() },
      { id: '3', sessionId: 's2', pageUrl: '/blog/post-1', eventType: 'time_spent', value: 75, createdAt: new Date() },
      { id: '4', sessionId: 's3', pageUrl: '/features', eventType: 'visit', value: 1, createdAt: new Date() },
      { id: '5', sessionId: 's3', pageUrl: '/features', eventType: 'visit', value: 1, createdAt: new Date() },
    ];
    setEvents(initialEvents);
  }, []);

  // Pattern Detection Logic (Simulated PHP logic)
  const patterns = useMemo(() => {
    const detected: Pattern[] = [];
    
    // Rule 1: High Intent
    const pageVisits: Record<string, number> = {};
    events.filter(e => e.eventType === 'visit').forEach(e => {
      pageVisits[e.pageUrl] = (pageVisits[e.pageUrl] || 0) + 1;
    });

    Object.entries(pageVisits).forEach(([url, count]) => {
      if (count >= 2) {
        detected.push({
          id: `hi-${url}`,
          type: 'high_intent',
          pageUrl: url,
          condition: `Visited ${count} times`,
          message: `Users are revisiting your ${url.replace('/', '')} page. Suggest showing a discount popup.`
        });
      }
    });

    // Rule 2: Engaged
    const timeSpent: Record<string, number> = {};
    events.filter(e => e.eventType === 'time_spent').forEach(e => {
      timeSpent[e.pageUrl] = (timeSpent[e.pageUrl] || 0) + e.value;
    });

    Object.entries(timeSpent).forEach(([url, total]) => {
      if (total > 60) {
        detected.push({
          id: `en-${url}`,
          type: 'engaged',
          pageUrl: url,
          condition: `Spent ${total}s`,
          message: `Users are highly engaged with ${url.replace('/', '')}. Suggest a newsletter signup.`
        });
      }
    });

    return detected;
  }, [events]);

  const toggleRule = (pattern: Pattern) => {
    const ruleId = pattern.id;
    if (activeRules.some(r => r.id === ruleId)) {
      setActiveRules(activeRules.filter(r => r.id !== ruleId));
    } else {
      setActiveRules([...activeRules, {
        id: ruleId,
        pageUrl: pattern.pageUrl,
        type: pattern.type,
        message: 'Get 10% discount today!'
      }]);
    }
  };

  const simulateVisit = (url: string) => {
    const newEvent: Event = {
      id: Math.random().toString(),
      sessionId: 'user-' + Math.random().toString(36).substr(2, 4),
      pageUrl: url,
      eventType: 'visit',
      value: 1,
      createdAt: new Date()
    };
    setEvents(prev => [...prev, newEvent]);

    // Check for active automation
    const rule = activeRules.find(r => r.pageUrl === url);
    if (rule) {
      setPopupMessage(rule.message);
      setShowPopup(true);
    }
  };

  return (
    <div className="flex h-screen bg-[#f0f0f1] font-sans text-[#3c434a]">
      {/* WordPress Sidebar */}
      <div className="w-[160px] bg-[#1d2327] flex flex-col shrink-0">
        <div className="h-8 flex items-center px-4 mt-2">
          <div className="w-5 h-5 bg-[#c3c4c7] rounded-full flex items-center justify-center">
            <Settings size={12} className="text-[#1d2327]" />
          </div>
        </div>
        <div className="mt-4">
          <SidebarItem icon={LayoutDashboard} label="Dashboard" />
          <SidebarItem icon={MousePointer2} label="Posts" />
          <SidebarItem icon={MousePointer2} label="Media" />
          <SidebarItem icon={MousePointer2} label="Pages" />
          <div className="h-[1px] bg-[#3c434a] my-2 mx-4" />
          <SidebarItem 
            icon={Zap} 
            label="Silent Auto" 
            active={currentTab === 'dashboard'} 
            onClick={() => setCurrentTab('dashboard')}
          />
          <SidebarItem 
            icon={Code2} 
            label="File Explorer" 
            active={currentTab === 'files'} 
            onClick={() => setCurrentTab('files')}
          />
        </div>
      </div>

      {/* Main Content */}
      <div className="flex-1 overflow-y-auto">
        {/* WP Admin Bar */}
        <div className="h-8 bg-[#1d2327] flex items-center justify-between px-4 text-[#c3c4c7] text-[13px]">
          <div className="flex items-center gap-4">
            <span className="flex items-center gap-1 hover:text-[#72aee6] cursor-pointer">
              <Settings size={14} /> My Site
            </span>
            <span className="flex items-center gap-1 hover:text-[#72aee6] cursor-pointer">
              <Bell size={14} /> 0
            </span>
            <span className="flex items-center gap-1 hover:text-[#72aee6] cursor-pointer">
              <Plus size={14} /> New
            </span>
          </div>
          <div className="flex items-center gap-2">
            <span>Howdy, Admin</span>
            <div className="w-5 h-5 bg-[#c3c4c7] rounded-full" />
          </div>
        </div>

        <div className="p-8 max-w-6xl mx-auto">
          {currentTab === 'dashboard' ? (
            <motion.div 
              initial={{ opacity: 0, y: 10 }}
              animate={{ opacity: 1, y: 0 }}
              className="space-y-8"
            >
              <div className="flex justify-between items-end">
                <div>
                  <h1 className="text-[23px] font-normal text-[#1d2327] mb-1">Silent Automation Dashboard</h1>
                  <p className="text-[#646970]">Tracking and automating user behavior in real-time.</p>
                </div>
                <div className="flex gap-2">
                  <button 
                    onClick={() => simulateVisit('/pricing')}
                    className="bg-[#2271b1] text-white px-4 py-1.5 rounded text-[13px] font-medium hover:bg-[#135e96] transition-colors flex items-center gap-2"
                  >
                    Simulate Pricing Visit <ExternalLink size={14} />
                  </button>
                  <button 
                    onClick={() => simulateVisit('/features')}
                    className="bg-white border border-[#2271b1] text-[#2271b1] px-4 py-1.5 rounded text-[13px] font-medium hover:bg-[#f6f7f7] transition-colors"
                  >
                    Simulate Features Visit
                  </button>
                </div>
              </div>

              {/* Stats */}
              <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                <StatCard title="Total Tracked Events" value={events.length} icon={BarChart3} />
                <StatCard title="Active Automations" value={activeRules.length} icon={Zap} />
                <StatCard title="Unique Visitors" value={new Set(events.map(e => e.sessionId)).size} icon={Users} />
              </div>

              {/* Suggestions Table */}
              <div className="bg-white border border-[#ccd0d4] shadow-sm overflow-hidden">
                <div className="px-5 py-4 border-b border-[#ccd0d4] bg-[#f6f7f7]">
                  <h2 className="text-[14px] font-semibold text-[#1d2327]">Detected Patterns & Suggestions</h2>
                </div>
                <div className="overflow-x-auto">
                  <table className="w-full text-left border-collapse">
                    <thead>
                      <tr className="bg-white border-b border-[#ccd0d4]">
                        <th className="px-5 py-3 text-[13px] font-semibold text-[#1d2327]">Pattern</th>
                        <th className="px-5 py-3 text-[13px] font-semibold text-[#1d2327]">Condition</th>
                        <th className="px-5 py-3 text-[13px] font-semibold text-[#1d2327]">Suggestion</th>
                        <th className="px-5 py-3 text-[13px] font-semibold text-[#1d2327] text-right">Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      {patterns.length === 0 ? (
                        <tr>
                          <td colSpan={4} className="px-5 py-8 text-center text-[#646970] italic">
                            No patterns detected yet. Simulate some visits to see suggestions!
                          </td>
                        </tr>
                      ) : (
                        patterns.map((pattern) => {
                          const isActive = activeRules.some(r => r.id === pattern.id);
                          return (
                            <tr key={pattern.id} className="border-b border-[#f0f0f1] hover:bg-[#f6f7f7] transition-colors">
                              <td className="px-5 py-4">
                                <span className="text-[13px] font-bold text-[#1d2327]">
                                  {pattern.type === 'high_intent' ? 'High Intent' : 'Engaged'}
                                </span>
                              </td>
                              <td className="px-5 py-4 text-[13px] text-[#3c434a]">{pattern.condition}</td>
                              <td className="px-5 py-4 text-[13px] text-[#3c434a]">{pattern.message}</td>
                              <td className="px-5 py-4 text-right">
                                <button 
                                  onClick={() => toggleRule(pattern)}
                                  className={`px-4 py-1.5 rounded text-[13px] font-medium transition-all ${
                                    isActive 
                                    ? 'bg-white border border-[#d63638] text-[#d63638] hover:bg-[#fcf0f1]' 
                                    : 'bg-[#2271b1] text-white hover:bg-[#135e96]'
                                  }`}
                                >
                                  {isActive ? 'Deactivate' : 'Activate Automation'}
                                </button>
                              </td>
                            </tr>
                          );
                        })
                      )}
                    </tbody>
                  </table>
                </div>
              </div>

              {/* Active Rules Section */}
              {activeRules.length > 0 && (
                <div className="bg-white border border-[#ccd0d4] shadow-sm p-5">
                  <h3 className="text-[14px] font-semibold text-[#1d2327] mb-4 flex items-center gap-2">
                    <CheckCircle2 size={16} className="text-[#00a32a]" /> Active Automations
                  </h3>
                  <div className="space-y-3">
                    {activeRules.map(rule => (
                      <div key={rule.id} className="flex items-center justify-between p-3 bg-[#f6f7f7] border border-[#ccd0d4] rounded">
                        <div>
                          <span className="text-[12px] font-bold uppercase tracking-wider text-[#646970] block mb-1">
                            Popup on {rule.pageUrl}
                          </span>
                          <span className="text-[13px] text-[#1d2327]">"{rule.message}"</span>
                        </div>
                        <div className="flex items-center gap-2 text-[#00a32a] text-[12px] font-medium">
                          <div className="w-2 h-2 bg-[#00a32a] rounded-full animate-pulse" />
                          Live
                        </div>
                      </div>
                    ))}
                  </div>
                </div>
              )}
            </motion.div>
          ) : (
            <motion.div 
              initial={{ opacity: 0 }}
              animate={{ opacity: 1 }}
              className="space-y-6"
            >
              <h1 className="text-[23px] font-normal text-[#1d2327] mb-1">Plugin File Structure</h1>
              <p className="text-[#646970]">The following files have been created in the <code>/silent-automation</code> directory.</p>
              
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <FileBox 
                  title="Core Files" 
                  icon={Database}
                  files={[
                    'silent-automation.php',
                    'includes/class-silent-automation-db.php',
                    'includes/class-silent-automation-api.php',
                    'includes/class-silent-automation-tracker.php'
                  ]} 
                />
                <FileBox 
                  title="Admin & Public" 
                  icon={LayoutDashboard}
                  files={[
                    'admin/class-silent-automation-admin.php',
                    'public/class-silent-automation-public.php'
                  ]} 
                />
                <FileBox 
                  title="Assets (JS/CSS)" 
                  icon={FileJson}
                  files={[
                    'assets/js/tracker.js',
                    'assets/js/admin.js',
                    'assets/css/admin.css',
                    'assets/css/public.css'
                  ]} 
                />
                <div className="bg-[#2271b1] text-white p-5 rounded shadow-sm">
                  <h3 className="font-bold mb-2 flex items-center gap-2"><Zap size={18} /> Installation Guide</h3>
                  <ol className="text-[13px] space-y-2 list-decimal list-inside opacity-90">
                    <li>Download the <code>silent-automation</code> folder.</li>
                    <li>Upload it to <code>wp-content/plugins/</code>.</li>
                    <li>Activate via WordPress Admin.</li>
                    <li>The custom table will be created automatically.</li>
                  </ol>
                </div>
              </div>
            </motion.div>
          )}
        </div>
      </div>

      {/* Frontend Simulation Popup */}
      <AnimatePresence>
        {showPopup && (
          <motion.div 
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            className="fixed inset-0 bg-black/50 flex items-center justify-center z-[99999] p-4"
          >
            <motion.div 
              initial={{ scale: 0.9, y: 20 }}
              animate={{ scale: 1, y: 0 }}
              exit={{ scale: 0.9, y: 20 }}
              className="bg-white rounded-lg shadow-2xl p-8 max-w-sm w-full relative text-center"
            >
              <button 
                onClick={() => setShowPopup(false)}
                className="absolute top-4 right-4 text-[#646970] hover:text-[#1d2327]"
              >
                <XCircle size={24} />
              </button>
              <div className="w-16 h-16 bg-[#fcf0f1] text-[#d63638] rounded-full flex items-center justify-center mx-auto mb-4">
                <Zap size={32} />
              </div>
              <h2 className="text-xl font-bold text-[#1d2327] mb-2">Special Offer!</h2>
              <p className="text-[#646970] mb-6">{popupMessage}</p>
              <button 
                onClick={() => setShowPopup(false)}
                className="w-full bg-[#d63638] text-white py-3 rounded-md font-bold hover:bg-[#b32d2e] transition-colors"
              >
                Claim Now
              </button>
            </motion.div>
          </motion.div>
        )}
      </AnimatePresence>
    </div>
  );
}

const FileBox = ({ title, files, icon: Icon }: any) => (
  <div className="bg-white border border-[#ccd0d4] p-5 shadow-sm">
    <h3 className="text-[14px] font-semibold text-[#1d2327] mb-3 flex items-center gap-2 border-b pb-2 border-[#f0f0f1]">
      <Icon size={16} className="text-[#2271b1]" /> {title}
    </h3>
    <ul className="space-y-1.5">
      {files.map((f: string) => (
        <li key={f} className="text-[13px] text-[#646970] flex items-center gap-2">
          <ChevronRight size={12} className="text-[#ccd0d4]" />
          <code>{f}</code>
        </li>
      ))}
    </ul>
  </div>
);

