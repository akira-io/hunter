import { Badge } from '@/components/ui/badge';
import { JSX } from 'react';

import { Option } from '@/components/ui/multiselect';
import {
    FaAngular,
    FaAws,
    FaBalanceScale,
    FaBinoculars,
    FaBug,
    FaClipboardCheck,
    FaClipboardList,
    FaCloud,
    FaCode,
    FaDatabase,
    FaDocker,
    FaExclamationTriangle,
    FaFire,
    FaJava,
    FaJsSquare,
    FaLaravel,
    FaLightbulb,
    FaLock,
    FaNodeJs,
    FaPhp,
    FaPython,
    FaReact,
    FaRust,
    FaSearch,
    FaServer,
    FaShieldAlt,
    FaSwift,
    FaSymfony,
    FaVuejs,
} from 'react-icons/fa';
import { PiFileCSharp } from 'react-icons/pi';
import {
    SiCplusplus,
    SiDjango,
    SiDotnet,
    SiFirebase,
    SiFlask,
    SiGo,
    SiKotlin,
    SiKubernetes,
    SiMongodb,
    SiMysql,
    SiNestjs,
    SiPostgresql,
    SiRedis,
    SiRuby,
    SiSpring,
    SiTypescript,
} from 'react-icons/si';

export function HighlightedSkills({ techs }: { techs: Option[] }) {
    const skillIcons: Record<string, JSX.Element> = {
        // Languages
        JavaScript: <FaJsSquare className="text-yellow-400" />,
        TypeScript: <SiTypescript className="text-blue-500" />,
        Python: <FaPython className="text-blue-400" />,
        PHP: <FaPhp className="text-purple-600" />,
        Go: <SiGo className="text-cyan-600" />,
        Rust: <FaRust className="text-orange-600" />,
        Java: <FaJava className="text-red-600" />,
        Kotlin: <SiKotlin className="text-purple-400" />,
        Swift: <FaSwift className="text-orange-500" />,
        Ruby: <SiRuby className="text-pink-500" />,
        'C++': <SiCplusplus className="text-blue-700" />,
        'C#': <PiFileCSharp className="text-green-700" />,

        // Front-end
        React: <FaReact className="text-sky-400" />,
        Vue: <FaVuejs className="text-green-500" />,
        Angular: <FaAngular className="text-red-500" />,
        Svelte: <FaJsSquare className="text-orange-400" />, // Placeholder
        NextJs: <FaReact className="text-black" />,
        NuxtJs: <FaVuejs className="text-green-500" />,

        // Back-end
        Laravel: <FaLaravel className="text-rose-600" />,
        Symfony: <FaSymfony className="text-gray-600" />,
        NodeJs: <FaNodeJs className="text-green-600" />,
        Express: <FaNodeJs className="text-gray-500" />,
        NestJs: <SiNestjs className="text-red-500" />,
        Django: <SiDjango className="text-green-800" />,
        Flask: <SiFlask className="text-black" />,
        Spring: <SiSpring className="text-green-500" />,
        '.NET': <SiDotnet className="text-purple-700" />,

        // Infrastructure
        Docker: <FaDocker className="text-blue-500" />,
        Kubernetes: <SiKubernetes className="text-blue-400" />,
        Firebase: <SiFirebase className="text-yellow-500" />,
        AWS: <FaAws className="text-orange-500" />,
        Azure: <FaAws className="text-blue-700" />, // Placeholder
        GCP: <FaAws className="text-blue-400" />, // Placeholder

        // Databases
        MySQL: <SiMysql className="text-orange-500" />,
        PostgreSQL: <SiPostgresql className="text-blue-600" />,
        SQLite: <FaDatabase className="text-gray-600" />,
        MongoDB: <SiMongodb className="text-green-600" />,
        Redis: <SiRedis className="text-red-600" />,

        // Cybersecurity
        PenTesting: <FaRust className="text-rose-500" />, // Escolhi Rust por ter cor forte (podemos mudar se quiser)
        EthicalHacking: <FaPython className="text-green-500" />,
        NetworkSecurity: <FaNodeJs className="text-blue-500" />,
        WebSecurity: <FaReact className="text-sky-500" />,
        MalwareAnalysis: <FaBug className="text-red-500" />,
        IncidentResponse: <FaFire className="text-orange-600" />,
        ThreatHunting: <FaBinoculars className="text-gray-700" />,
        VulnerabilityAssessment: <FaShieldAlt className="text-blue-600" />,
        SecurityAuditing: <FaClipboardCheck className="text-green-600" />,
        RiskManagement: <FaBalanceScale className="text-purple-500" />,
        Compliance: <FaClipboardList className="text-purple-500" />,
        IncidentManagement: <FaExclamationTriangle className="text-yellow-500" />,
        DigitalForensics: <FaSearch className="text-gray-400" />,
        SecurityAwareness: <FaLightbulb className="text-yellow-400" />,
        CloudSecurity: <FaCloud className="text-blue-400" />,
        ApplicationSecurity: <FaLock className="text-gray-600" />,
        DataProtection: <FaDatabase className="text-green-700" />,
        SIEM: <FaServer className="text-purple-600" />,
    };

    return (
        <>
            {techs.map((tech, index) => (
                <Badge key={tech.value + index} variant="outline" className="items-center gap-1.5">
                    {skillIcons[tech.label] || <FaCode className="text-muted-foreground" />}
                    <span>{tech.label}</span>
                </Badge>
            ))}
        </>
    );
}
